<?php

namespace Tests\Feature;

use App\Livewire\Employee\EmployeePunchPad;
use App\Models\Attendance;
use App\Models\EmployeeShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class MultiShiftAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_checks_into_correct_shift_when_multiple_same_day_shifts_exist(): void
    {
        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00')); // Monday

        $user = User::factory()->create();

        $morningShift = EmployeeShift::create([
            'user_id' => $user->id,
            'day_of_week' => 'mon',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 10,
            'break_allowance_minutes' => 60,
        ]);

        $eveningShift = EmployeeShift::create([
            'user_id' => $user->id,
            'day_of_week' => 'mon',
            'start_time' => '19:00',
            'end_time' => '23:00',
            'grace_period_minutes' => 5,
            'break_allowance_minutes' => 30,
        ]);

        Livewire::actingAs($user);

        Livewire::test(EmployeePunchPad::class)
            ->call('checkIn');

        $attendanceMorning = Attendance::where('employee_shift_id', $morningShift->id)->first();

        $this->assertNotNull($attendanceMorning);
        $this->assertEquals('2026-01-05', $attendanceMorning->date->toDateString());

        Carbon::setTestNow(Carbon::parse('2026-01-05 17:30:00'));
        Livewire::test(EmployeePunchPad::class)->call('checkOut');

        Carbon::setTestNow(Carbon::parse('2026-01-05 20:00:00'));
        Livewire::test(EmployeePunchPad::class)
            ->call('checkIn');

        $attendanceEvening = Attendance::where('employee_shift_id', $eveningShift->id)->first();

        $this->assertNotNull($attendanceEvening);
        $this->assertEquals('2026-01-05', $attendanceEvening->date->toDateString());
    }

    public function test_night_shift_anchors_to_previous_day_for_attendance_date(): void
    {
        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2026-01-06 02:00:00')); // Tuesday early morning

        $user = User::factory()->create();

        $nightShift = EmployeeShift::create([
            'user_id' => $user->id,
            'day_of_week' => 'mon',
            'start_time' => '19:00',
            'end_time' => '04:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 45,
        ]);

        Livewire::actingAs($user);

        Livewire::test(EmployeePunchPad::class)
            ->call('checkIn');

        $attendance = Attendance::where('employee_shift_id', $nightShift->id)->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('2026-01-05', $attendance->date->toDateString()); // shift start date (Monday)
    }
}
