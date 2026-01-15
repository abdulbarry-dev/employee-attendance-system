<?php

namespace App\Livewire\Employee;

use App\Models\EmployeePenalty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('My Profile')]
class EmployeeProfile extends Component
{
    public $selectedMonth;

    public $selectedYear;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function getEmployeeProperty()
    {
        return Auth::user()->loadMissing('shifts');
    }

    public function getPenaltiesProperty()
    {
        $startOfMonth = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return EmployeePenalty::where('user_id', Auth::id())
            ->whereBetween('occurred_on', [$startOfMonth, $endOfMonth])
            ->orderBy('occurred_on', 'desc')
            ->get();
    }

    public function getTotalPenaltyAmountProperty()
    {
        return $this->penalties->sum('penalty_amount');
    }

    public function getNetSalaryProperty()
    {
        if (! $this->employee->monthly_salary) {
            return 0;
        }

        return max(0, $this->employee->monthly_salary - $this->totalPenaltyAmount);
    }

    public function getWorkingDaysProperty()
    {
        $workingDays = $this->employee->shifts?->pluck('day_of_week')->unique()->values()->all();

        if (empty($workingDays)) {
            $workingDays = $this->employee->working_days ?? [];
        }

        $dayNames = [
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            'sat' => 'Saturday',
        ];

        return collect($workingDays)->map(fn ($day) => $dayNames[$day] ?? $day)->join(', ');
    }

    public function changeMonth($direction)
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1);

        if ($direction === 'prev') {
            $date->subMonth();
        } else {
            $date->addMonth();
        }

        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
    }

    public function render()
    {
        return view('livewire.employee.employee-profile', [
            'employee' => $this->employee,
            'penalties' => $this->penalties,
            'workingDays' => $this->workingDays,
            'totalPenaltyAmount' => $this->totalPenaltyAmount,
            'netSalary' => $this->netSalary,
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
        ]);
    }
}
