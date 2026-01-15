<?php

namespace Tests\Feature;

use App\Models\EmployeeMonthlyShift;
use App\Models\EmployeeShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeShiftsViewTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employee = User::factory()->create();
    }

    public function test_can_assign_shift_to_first_day_of_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-01'));

        $date = Carbon::parse('2026-02-01');

        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => $date,
            'employee_shift_id' => EmployeeShift::create([
                'user_id' => $this->employee->id,
                'name' => 'Standard',
                'day_of_week' => 'custom',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'grace_period_minutes' => 15,
                'break_allowance_minutes' => 30,
            ])->id,
        ]);

        // Verify the shift was created in database
        $this->assertTrue(
            EmployeeMonthlyShift::where('user_id', $this->employee->id)
                ->whereDate('date', '2026-02-01')
                ->exists()
        );
    }

    public function test_shift_is_retrievable_for_first_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-01'));

        $shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Standard',
            'day_of_week' => 'custom',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => '2026-02-01',
            'employee_shift_id' => $shift->id,
        ]);

        // Verify shift data exists in database and can be retrieved
        $monthlyShift = EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereDate('date', '2026-02-01')
            ->with('shift')
            ->first();

        $this->assertNotNull($monthlyShift);
        $this->assertNotNull($monthlyShift->shift);
        $this->assertEquals('09:00', $monthlyShift->shift->start_time->format('H:i'));
        $this->assertEquals('17:00', $monthlyShift->shift->end_time->format('H:i'));
    }

    public function test_shifts_are_persisted_for_all_days_including_first(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-01'));

        $shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Standard',
            'day_of_week' => 'custom',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        // Create shifts for day 1 and day 2
        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => '2026-02-01',
            'employee_shift_id' => $shift->id,
        ]);

        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => '2026-02-02',
            'employee_shift_id' => $shift->id,
        ]);

        // Both days should have shifts in database
        $shiftDay1 = EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereDate('date', '2026-02-01')
            ->with('shift')
            ->first();

        $shiftDay2 = EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereDate('date', '2026-02-02')
            ->with('shift')
            ->first();

        $this->assertNotNull($shiftDay1);
        $this->assertNotNull($shiftDay2);
        $this->assertNotNull($shiftDay1->shift);
        $this->assertNotNull($shiftDay2->shift);
    }

    public function test_can_update_shift_on_first_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-01'));

        $morningShift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Morning',
            'day_of_week' => 'custom',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        $afternoonShift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Afternoon',
            'day_of_week' => 'custom',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        // Create initial shift assignment
        $monthlyShift = EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => '2026-02-01',
            'employee_shift_id' => $morningShift->id,
        ]);

        // Update to different shift
        $monthlyShift->update(['employee_shift_id' => $afternoonShift->id]);

        // Verify the update
        $updated = EmployeeMonthlyShift::find($monthlyShift->id);
        $this->assertEquals($afternoonShift->id, $updated->employee_shift_id);
    }

    public function test_can_delete_shift_on_first_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-01'));

        $shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Morning',
            'day_of_week' => 'custom',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        $monthlyShift = EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => '2026-02-01',
            'employee_shift_id' => $shift->id,
        ]);

        $monthlyShift->delete();

        $this->assertDatabaseMissing('employee_monthly_shifts', [
            'user_id' => $this->employee->id,
            'date' => '2026-02-01',
        ]);
    }
}
