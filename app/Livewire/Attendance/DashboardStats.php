<?php

namespace App\Livewire\Attendance;

use Livewire\Component;

use App\Models\Attendance;
use App\Models\User;

class DashboardStats extends Component
{
    public function render()
    {
        $today = today();

        $stats = [
            'total_employees' => User::role('employee')->count(),
            'present' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'on_break' => Attendance::where('date', $today)->where('status', 'on_break')->count(),
            'completed' => Attendance::where('date', $today)->whereNotNull('check_out')->count(),
        ];


        $attendanceIds = Attendance::where('date', $today)->pluck('user_id');
        $stats['absent'] = $stats['total_employees'] - $attendanceIds->count();

        $latestAttendances = Attendance::with('user')
            ->where('date', $today)
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('livewire.attendance.dashboard-stats', [
            'stats' => $stats,
            'latestAttendances' => $latestAttendances
        ]);
    }
}
