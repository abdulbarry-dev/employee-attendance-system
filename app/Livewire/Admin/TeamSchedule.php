<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class TeamSchedule extends Component
{
    public function render()
    {
        $employees = User::role('employee')
            ->with('shifts')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.team-schedule', compact('employees'));
    }
}
