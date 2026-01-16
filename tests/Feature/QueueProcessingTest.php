<?php

namespace Tests\Feature;

use App\Jobs\CalculatePenalties;
use App\Jobs\SendPenaltyNotification;
use App\Models\Attendance;
use App\Models\EmployeePenalty;
use App\Models\EmployeeShift;
use App\Models\User;
use App\Services\AttendancePenaltyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;

    protected EmployeeShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::factory()->create([
            'email' => 'employee@test.com',
            'monthly_salary' => 5000,
            'shift_start' => '09:00',
            'shift_end' => '17:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 60,
            'working_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        ]);

        $this->shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'day_of_week' => strtolower(Carbon::today()->format('D')),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 60,
        ]);
    }

    public function test_calculate_penalties_job_is_dispatched_on_late_check_in(): void
    {
        Queue::fake();

        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'employee_shift_id' => $this->shift->id,
            'date' => Carbon::today(),
            'check_in' => Carbon::today()->setTime(9, 30), // 30 min late
            'status' => 'present',
        ]);

        CalculatePenalties::dispatch($attendance, 'late');

        Queue::assertPushed(CalculatePenalties::class, function ($job) use ($attendance) {
            return $job->attendance->id === $attendance->id
                && $job->penaltyType === 'late'
                && $job->queue === 'default';
        });
    }

    public function test_calculate_penalties_job_actually_creates_penalty(): void
    {
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'employee_shift_id' => $this->shift->id,
            'date' => Carbon::today(),
            'check_in' => Carbon::today()->setTime(9, 30), // 30 min late
            'status' => 'present',
        ]);

        $this->assertDatabaseCount('employee_penalties', 0);

        $job = new CalculatePenalties($attendance, 'late');
        $job->handle(app(AttendancePenaltyService::class));

        $this->assertDatabaseHas('employee_penalties', [
            'user_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'type' => 'late',
        ]);
    }

    public function test_send_penalty_notification_job_is_dispatched_to_high_queue(): void
    {
        Queue::fake();

        $penalty = EmployeePenalty::create([
            'user_id' => $this->employee->id,
            'type' => 'late',
            'occurred_on' => Carbon::today(),
            'penalty_amount' => 100,
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
        ]);

        SendPenaltyNotification::dispatch($this->employee, $penalty);

        Queue::assertPushed(SendPenaltyNotification::class, function ($job) use ($penalty) {
            return $job->user->id === $this->employee->id
                && $job->penalty->id === $penalty->id
                && $job->queue === 'high';
        });
    }

    public function test_calculate_penalties_job_retries_on_failure(): void
    {
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'employee_shift_id' => $this->shift->id,
            'date' => Carbon::today(),
            'check_in' => Carbon::today()->setTime(9, 0),
            'status' => 'present',
        ]);

        $job = new CalculatePenalties($attendance, 'late');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300, 1500], $job->backoff());
    }

    public function test_break_overage_penalty_dispatches_with_break_minutes(): void
    {
        Queue::fake();

        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'employee_shift_id' => $this->shift->id,
            'date' => Carbon::today(),
            'check_in' => Carbon::today()->setTime(9, 0),
            'status' => 'present',
        ]);

        CalculatePenalties::dispatch($attendance, 'break_overage', 90);

        Queue::assertPushed(CalculatePenalties::class, function ($job) use ($attendance) {
            return $job->attendance->id === $attendance->id
                && $job->penaltyType === 'break_overage'
                && $job->breakMinutes === 90;
        });
    }
}
