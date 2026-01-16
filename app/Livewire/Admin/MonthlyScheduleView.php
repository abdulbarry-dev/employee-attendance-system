<?php

namespace App\Livewire\Admin;

use App\Models\EmployeeMonthlyShift;
use App\Models\EmployeeShift;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class MonthlyScheduleView extends Component
{
    public User $employee;

    public int $year;

    public int $month;

    public ?int $selectedDateDay = null;

    public bool $showModal = false;

    public array $monthlyShiftsData = [];

    public int $refreshKey = 0;

    public ?string $customStartTime = null;

    public ?string $customEndTime = null;

    public function mount(User $employee): void
    {
        $this->employee = $employee->load(['shifts', 'monthlyShifts.shift']);
        $this->year = request()->query('year', Carbon::now()->year);
        $this->month = request()->query('month', Carbon::now()->month);

        $this->loadMonthlyShifts();
    }

    public function loadMonthlyShifts(): void
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $endDate = $startDate->clone()->endOfMonth()->endOfDay();

        $shifts = EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('shift')
            ->get();

        $this->monthlyShiftsData = $shifts->map(function ($shift) {
            return [
                'id' => $shift->id,
                'date' => $shift->date->toDateString(),
                'shift_id' => $shift->employee_shift_id,
                'shift_name' => $shift->shift?->name,
                'start_time' => $shift->shift?->start_time->format('H:i'),
                'end_time' => $shift->shift?->end_time->format('H:i'),
            ];
        })->toArray();
    }

    /**
     * Get calendar grid array for current month.
     * Returns array of weeks, each week is array of 7 days.
     */
    public function getCalendarGrid(): array
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1);
        $endDate = $startDate->clone()->endOfMonth();

        $calendar = [];
        $firstDayOfWeek = $startDate->dayOfWeek; // 0 = Sunday

        // Initialize grid with empty cells before month starts
        $week = array_fill(0, 7, null);
        $dayCounter = $firstDayOfWeek;

        // Fill in the days
        for ($day = 1; $day <= $endDate->day; $day++) {
            $week[$dayCounter] = [
                'day' => $day,
                'date' => Carbon::createFromDate($this->year, $this->month, $day),
                'isCurrentMonth' => true,
            ];

            $dayCounter++;

            if ($dayCounter === 7) {
                $calendar[] = $week;
                $week = array_fill(0, 7, null);
                $dayCounter = 0;
            }
        }

        // Fill remaining cells with next month's dates if needed
        if ($dayCounter > 0) {
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Get shift for a specific date.
     */
    public function getShiftForDate(Carbon $date): ?object
    {
        $dateString = $date->toDateString();
        $shift = collect($this->monthlyShiftsData)->first(fn ($s) => $s['date'] === $dateString);

        if ($shift) {
            return (object) [
                'shift' => (object) [
                    'name' => $shift['shift_name'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                ],
                'monthlyShift' => $shift,
            ];
        }

        return null;
    }

    /**
     * Open modal to select shift for date.
     */
    public function selectDate(int $day): void
    {
        $this->selectedDateDay = $day;
        $date = Carbon::createFromDate($this->year, $this->month, $day);
        $dateString = $date->toDateString();

        $shift = collect($this->monthlyShiftsData)->first(fn ($s) => $s['date'] === $dateString);

        if ($shift) {
            $this->customStartTime = $shift['start_time'];
            $this->customEndTime = $shift['end_time'];
        } else {
            $this->customStartTime = '09:00';
            $this->customEndTime = '17:00';
        }

        $this->showModal = true;
    }

    /**
     * Save shift assignment for selected date.
     */
    public function assignShiftToDate(): void
    {
        if ($this->selectedDateDay === null) {
            return;
        }

        $date = Carbon::createFromDate($this->year, $this->month, $this->selectedDateDay);
        $dateString = $date->toDateString();

        // If times are provided, create/update shift
        if ($this->customStartTime && $this->customEndTime) {
            $this->validate([
                'customStartTime' => 'required|date_format:H:i',
                'customEndTime' => 'required|date_format:H:i',
            ]);

            $customShift = $this->findOrCreateCustomShift();

            $existingShift = EmployeeMonthlyShift::where('user_id', $this->employee->id)
                ->whereDate('date', '=', $dateString)
                ->first();

            if ($existingShift) {
                $existingShift->update([
                    'employee_shift_id' => $customShift->id,
                ]);
            } else {
                EmployeeMonthlyShift::create([
                    'user_id' => $this->employee->id,
                    'date' => $dateString,
                    'employee_shift_id' => $customShift->id,
                ]);
            }
        } else {
            // Remove shift if times are empty
            EmployeeMonthlyShift::where('user_id', $this->employee->id)
                ->whereDate('date', $date)
                ->delete();
        }

        $this->loadMonthlyShifts();
        $this->refreshKey++;
        $this->closeModal();
    }

    /**
     * Find or create a custom shift for the employee.
     */
    protected function findOrCreateCustomShift(): EmployeeShift
    {
        // Generate shift name based on time range
        $name = 'Custom '.$this->customStartTime.'-'.$this->customEndTime;

        // Use employee's default grace period and break allowance
        $gracePeriod = $this->employee->grace_period_minutes ?? 15;
        $breakAllowance = $this->employee->break_allowance_minutes ?? 30;

        $existing = EmployeeShift::query()
            ->where('user_id', $this->employee->id)
            ->where('day_of_week', 'custom')
            ->whereTime('start_time', '=', Carbon::createFromFormat('H:i', $this->customStartTime)->format('H:i:s'))
            ->whereTime('end_time', '=', Carbon::createFromFormat('H:i', $this->customEndTime)->format('H:i:s'))
            ->where('grace_period_minutes', $gracePeriod)
            ->where('break_allowance_minutes', $breakAllowance)
            ->first();

        if ($existing) {
            return $existing;
        }

        return EmployeeShift::create([
            'user_id' => $this->employee->id,
            'name' => $name,
            'day_of_week' => 'custom',
            'start_time' => $this->customStartTime,
            'end_time' => $this->customEndTime,
            'grace_period_minutes' => $gracePeriod,
            'break_allowance_minutes' => $breakAllowance,
            'is_active' => true,
        ]);
    }

    /**
     * Close modal and reset selections.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedDateDay = null;
    }

    /**
     * Delete shift assignment for selected date.
     */
    public function deleteShiftForDate(): void
    {
        if ($this->selectedDateDay === null) {
            return;
        }

        $date = Carbon::createFromDate($this->year, $this->month, $this->selectedDateDay);
        $dateString = $date->toDateString();

        EmployeeMonthlyShift::where('user_id', $this->employee->id)
            ->whereDate('date', '=', $dateString)
            ->delete();

        $this->loadMonthlyShifts();
        $this->refreshKey++;
        $this->closeModal();
    }

    /**
     * Navigate to previous month.
     */
    public function previousMonth(): void
    {
        $employeeCreated = Carbon::parse($this->employee->created_at);
        $employeeCreatedMonthStart = $employeeCreated->startOfMonth();

        if ($this->month === 1) {
            $newYear = $this->year - 1;
            $newMonth = 12;
        } else {
            $newYear = $this->year;
            $newMonth = $this->month - 1;
        }

        // Calculate target date for the previous month
        $targetDate = Carbon::createFromDate($newYear, $newMonth, 1);

        // Prevent navigating to months before employee creation
        if ($targetDate->isBefore($employeeCreatedMonthStart)) {
            return;
        }

        $this->year = $newYear;
        $this->month = $newMonth;

        $this->loadMonthlyShifts();
        $this->refreshKey++;
    }

    /**
     * Navigate to next month.
     */
    public function nextMonth(): void
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }

        $this->loadMonthlyShifts();
        $this->refreshKey++;
    }

    /**
     * Go to today's month.
     */
    public function goToToday(): void
    {
        $today = Carbon::now();
        $this->year = $today->year;
        $this->month = $today->month;
        $this->loadMonthlyShifts();
        $this->refreshKey++;
    }

    public function render()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1);
        $monthName = __($date->format('F')).' '.$date->format('Y');

        return view('livewire.admin.monthly-schedule-view', [
            'calendar' => $this->getCalendarGrid(),
            'monthName' => $monthName,
            'selectedDate' => $this->selectedDateDay
                ? Carbon::createFromDate($this->year, $this->month, $this->selectedDateDay)
                : null,
        ]);
    }
}
