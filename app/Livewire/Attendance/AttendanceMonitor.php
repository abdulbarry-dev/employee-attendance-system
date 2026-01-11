<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Attendance Monitor')]
class AttendanceMonitor extends Component
{
    public function render()
    {
        $today = today();

        // Calculate Stats
        $stats = [
            'total_employees' => User::role('employee')->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })->count(),
            'present' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'on_break' => Attendance::where('date', $today)->where('status', 'on_break')->count(),
            'completed' => Attendance::where('date', $today)->whereNotNull('check_out')->count(),
        ];

        $attendanceIds = Attendance::where('date', $today)->pluck('user_id');
        $stats['absent'] = max(0, $stats['total_employees'] - $attendanceIds->unique()->count());

        // Latest Activities
        $latestActivities = Attendance::with('user')
            ->where('date', $today)
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('livewire.attendance.attendance-monitor', [
            'stats' => $stats,
            'latestActivities' => $latestActivities,
        ]);
    }
}
