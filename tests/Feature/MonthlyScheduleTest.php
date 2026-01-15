<?php

namespace Tests\Feature;

use App\Models\EmployeeMonthlyShift;
use App\Models\EmployeeShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonthlyScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->employee = User::factory()->create();
    }

    public function test_monthly_schedule_page_loads(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->assertStatus(200);
    }

    public function test_calendar_grid_displays_current_month(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->assertSee(date('F Y'));
    }

    public function test_can_select_date_and_open_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->call('selectDate', 15)
            ->assertSet('showModal', true)
            ->assertSet('selectedDateDay', 15);
    }

    public function test_can_assign_shift_to_date(): void
    {
        $date = Carbon::now();

        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->call('selectDate', $date->day)
            ->set('customStartTime', '09:00')
            ->set('customEndTime', '17:00')
            ->call('assignShiftToDate')
            ->assertSet('showModal', false);

        $this->assertTrue(
            EmployeeMonthlyShift::where('user_id', $this->employee->id)
                ->whereDate('date', $date->toDateString())
                ->exists()
        );
    }

    public function test_can_update_existing_shift_assignment(): void
    {
        $date = Carbon::now();

        $shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Morning',
            'day_of_week' => 'custom',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => $date,
            'employee_shift_id' => $shift->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->call('selectDate', $date->day)
            ->set('customStartTime', '10:00')
            ->set('customEndTime', '18:00')
            ->call('assignShiftToDate');

        $updated = EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereDate('date', $date->toDateString())
            ->with('shift')
            ->first();

        $this->assertNotNull($updated);
        $this->assertEquals('10:00', $updated->shift->start_time->format('H:i'));
        $this->assertEquals('18:00', $updated->shift->end_time->format('H:i'));
    }

    public function test_can_remove_shift_assignment(): void
    {
        $date = Carbon::now();

        $shift = EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => 'Morning',
            'day_of_week' => 'custom',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'grace_period_minutes' => 15,
            'break_allowance_minutes' => 30,
        ]);

        EmployeeMonthlyShift::create([
            'user_id' => $this->employee->id,
            'date' => $date,
            'employee_shift_id' => $shift->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->call('selectDate', $date->day)
            ->call('deleteShiftForDate');

        $this->assertDatabaseMissing('employee_monthly_shifts', [
            'user_id' => $this->employee->id,
            'date' => $date->toDateString(),
        ]);
    }

    public function test_can_close_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee])
            ->call('selectDate', 15)
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('selectedDateDay', null);
    }

    public function test_can_navigate_to_previous_month(): void
    {
        // Create employee early enough that previous month navigation works
        $this->employee->forceFill(['created_at' => Carbon::parse('2025-01-01')])->save();

        Carbon::setTestNow(Carbon::parse('2026-03-15'));

        $component = Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee]);

        $initialMonth = $component->get('month');
        $initialYear = $component->get('year');

        $component->call('previousMonth');

        $newMonth = $component->get('month');
        $newYear = $component->get('year');

        if ($initialMonth === 1) {
            $this->assertEquals(12, $newMonth);
            $this->assertEquals($initialYear - 1, $newYear);
        } else {
            $this->assertEquals($initialMonth - 1, $newMonth);
            $this->assertEquals($initialYear, $newYear);
        }
    }

    public function test_can_navigate_to_next_month(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee]);

        $currentMonth = $component->get('month');
        $currentYear = $component->get('year');

        $component->call('nextMonth');

        if ($currentMonth === 12) {
            $this->assertEquals(1, $component->get('month'));
            $this->assertEquals($currentYear + 1, $component->get('year'));
        } else {
            $this->assertEquals($currentMonth + 1, $component->get('month'));
            $this->assertEquals($currentYear, $component->get('year'));
        }
    }

    public function test_can_go_to_today(): void
    {
        $today = Carbon::now();

        $component = Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee]);

        $component->call('goToToday');

        $this->assertEquals($today->month, $component->get('month'));
        $this->assertEquals($today->year, $component->get('year'));
    }

    public function test_shift_is_retrievable_for_assigned_date(): void
    {
        $date = Carbon::now();

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
            'date' => $date,
            'employee_shift_id' => $shift->id,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test('admin.monthly-schedule-view', ['employee' => $this->employee]);

        $shiftInfo = $component->get('monthlyShiftsData');

        $this->assertNotEmpty($shiftInfo);
        $this->assertEquals($date->toDateString(), $shiftInfo[0]['date']);
        $this->assertEquals('09:00', $shiftInfo[0]['start_time']);
        $this->assertEquals('17:00', $shiftInfo[0]['end_time']);
    }
}
