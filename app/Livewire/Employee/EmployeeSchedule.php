<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('My Schedule')]
class EmployeeSchedule extends Component
{
    #[Computed]
    public function employee()
    {
        return Auth::user()->loadMissing('shifts');
    }

    #[Computed]
    public function shiftsByDay()
    {
        $daysOrder = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

        $dayNames = [
            'sun' => __('Sunday'),
            'mon' => __('Monday'),
            'tue' => __('Tuesday'),
            'wed' => __('Wednesday'),
            'thu' => __('Thursday'),
            'fri' => __('Friday'),
            'sat' => __('Saturday'),
        ];

        return $this->employee->shifts
            ->where('is_active', true)
            ->sortBy(function ($shift) use ($daysOrder) {
                return [$daysOrder[$shift->day_of_week] ?? 999, $shift->start_time];
            })
            ->groupBy('day_of_week')
            ->map(function ($shifts, $day) use ($dayNames) {
                return [
                    'day' => $day,
                    'name' => $dayNames[$day] ?? ucfirst($day),
                    'shifts' => $shifts,
                ];
            })
            ->sortBy(function ($group) use ($daysOrder) {
                return $daysOrder[$group['day']] ?? 999;
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.employee.employee-schedule', [
            'employee' => $this->employee,
            'shiftsByDay' => $this->shiftsByDay,
        ]);
    }
}
