<?php

namespace App\Livewire\Employee;

use App\Models\Attendance;
use App\Models\EmployeePenalty;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Salary History')]
class EmployeeSalaryHistory extends Component
{
    public $selectedMonth;

    public $selectedYear;

    public function mount()
    {
        $user = Auth::user();

        // Get first attendance date to determine initial month
        $firstAttendance = Attendance::where('user_id', $user->id)
            ->oldest('date')
            ->first();

        // Start with current month, but if it's before registration, start with registration month
        if ($firstAttendance) {
            $registrationDate = $firstAttendance->date;
            $now = now();

            // If current month is before registration, start with registration month
            if ($now->isBefore($registrationDate->startOfMonth())) {
                $this->selectedMonth = $registrationDate->month;
                $this->selectedYear = $registrationDate->year;
            } else {
                $this->selectedMonth = $now->month;
                $this->selectedYear = $now->year;
            }
        } else {
            $this->selectedMonth = now()->month;
            $this->selectedYear = now()->year;
        }
    }

    /**
     * Check if next month navigation is enabled
     */
    public function canNavigateNext()
    {
        $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $currentDate = now()->startOfMonth();

        return $selectedDate->isBefore($currentDate);
    }

    /**
     * Check if previous month navigation is enabled
     */
    public function canNavigatePrev()
    {
        $user = Auth::user();
        $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);

        // Get first month with attendance
        $firstAttendance = Attendance::where('user_id', $user->id)
            ->oldest('date')
            ->first();

        if (! $firstAttendance) {
            return false; // No attendance records, cannot navigate back
        }

        $firstAttendanceMonth = $firstAttendance->date->startOfMonth();

        // Only allow navigation if selected date is after the first attendance month
        return $selectedDate->isAfter($firstAttendanceMonth);
    }

    /**
     * Calculate prorated salary based on working days and attendance
     */
    public function calculateProratedSalary($startOfMonth, $endOfMonth, $monthlySalary)
    {
        $user = Auth::user();

        // Get working days configuration - stored as lowercase short codes ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']
        $workingDaysConfig = $user->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];

        // Ensure array format and lowercase
        if (is_array($workingDaysConfig)) {
            $workingDaysConfig = array_map('strtolower', $workingDaysConfig);
        } else {
            $workingDaysConfig = ['mon', 'tue', 'wed', 'thu', 'fri'];
        }

        // Count total working days in the month
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $totalWorkingDays = 0;

        foreach ($period as $date) {
            $dayKey = strtolower($date->format('D')); // Returns: 'Mon', 'Tue', etc. -> lowercase: 'mon', 'tue', ...
            if (in_array($dayKey, $workingDaysConfig, true)) {
                $totalWorkingDays++;
            }
        }

        // Count actual attendance days for this user in this month
        $attendanceDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();

        // Calculate prorated salary
        $dailySalary = $totalWorkingDays > 0 ? $monthlySalary / $totalWorkingDays : 0;
        $proratedSalary = $dailySalary * $attendanceDays;

        return [
            'prorated_salary' => round($proratedSalary, 2),
            'total_working_days' => $totalWorkingDays,
            'attendance_days' => $attendanceDays,
            'daily_salary' => round($dailySalary, 2),
        ];
    }

    public function changeMonth($direction)
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1);

        if ($direction === 'prev') {
            $date->subMonth();

            // Check if we can go back before modifying state
            if (! $this->canNavigatePrev()) {
                return; // Don't navigate if at the first attendance month
            }
        } else {
            $date->addMonth();

            // Check if we can go forward before modifying state
            if (! $this->canNavigateNext()) {
                return; // Don't navigate if at or beyond current month
            }
        }

        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
    }

    public function render()
    {
        $user = Auth::user();
        $monthlySalary = (float) ($user->monthly_salary ?? 0);

        // Get first attendance date as registration date
        $firstAttendance = Attendance::where('user_id', $user->id)
            ->oldest('date')
            ->first();

        $registrationDate = $firstAttendance ? $firstAttendance->date : null;

        // Get penalties for all months
        $penalties = EmployeePenalty::where('user_id', $user->id)
            ->orderBy('occurred_on', 'desc')
            ->get();

        $grouped = $penalties->groupBy(fn ($penalty) => $penalty->occurred_on->format('Y-m'));

        $months = $grouped->map(function ($items, $key) use ($monthlySalary) {
            $date = Carbon::createFromFormat('Y-m', $key)->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $penaltyTotal = (float) $items->sum('penalty_amount');

            // Calculate prorated salary for this month
            $proratedData = $this->calculateProratedSalary($date, $endOfMonth, $monthlySalary);

            return [
                'key' => $key,
                'label' => $date->format('F Y'),
                'gross' => $monthlySalary,
                'prorated_gross' => $proratedData['prorated_salary'],
                'penalties' => $penaltyTotal,
                'net' => max(0, $proratedData['prorated_salary'] - $penaltyTotal),
                'late_minutes' => $items->sum('minutes_late'),
                'break_overage_minutes' => $items->sum('break_overage_minutes'),
                'entries' => $items->count(),
                'attendance_days' => $proratedData['attendance_days'],
                'working_days' => $proratedData['total_working_days'],
                'items' => $items,
            ];
        })->sortKeysDesc()->values();

        // Get penalties for selected month
        $startOfMonth = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $currentMonthPenalties = EmployeePenalty::where('user_id', $user->id)
            ->whereBetween('occurred_on', [$startOfMonth, $endOfMonth])
            ->orderBy('occurred_on', 'desc')
            ->get();

        $currentMonthTotal = (float) $currentMonthPenalties->sum('penalty_amount');

        // Calculate prorated salary for selected month
        $currentProratedData = $this->calculateProratedSalary($startOfMonth, $endOfMonth, $monthlySalary);
        $currentProratedSalary = $currentProratedData['prorated_salary'];
        $currentWorkingDays = $currentProratedData['total_working_days'];
        $currentAttendanceDays = $currentProratedData['attendance_days'];
        $currentDailySalary = $currentProratedData['daily_salary'];

        return view('livewire.employee.employee-salary-history', [
            'user' => $user,
            'monthlySalary' => $monthlySalary,
            'currentProratedSalary' => $currentProratedSalary,
            'currentWorkingDays' => $currentWorkingDays,
            'currentAttendanceDays' => $currentAttendanceDays,
            'currentDailySalary' => $currentDailySalary,
            'months' => $months,
            'currentMonthPenalties' => $currentMonthPenalties,
            'currentMonthTotal' => $currentMonthTotal,
            'currentNetSalary' => max(0, $currentProratedSalary - $currentMonthTotal),
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'canNavigatePrev' => $this->canNavigatePrev(),
            'canNavigateNext' => $this->canNavigateNext(),
            'registrationDate' => $registrationDate,
        ]);
    }
}
