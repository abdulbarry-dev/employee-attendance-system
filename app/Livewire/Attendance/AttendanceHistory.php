<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use App\Models\Attendance;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Attendance History')]
class AttendanceHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $attendances = Attendance::with(['user', 'breaks'])
            ->latest('date')
            ->latest('check_in')
            ->paginate(5);

        return view('livewire.attendance.attendance-history', [
            'attendances' => $attendances
        ]);
    }
}
