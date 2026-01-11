<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Services\AttendancePenaltyService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
#[Title('Attendance Punch')]
class EmployeePunchPad extends Component
{
    public $attendance;
    public $currentBreak;
    public $currentTime;

    // Geolocation data
    public $latitude;
    public $longitude;
    public $error;

    public function mount()
    {
        $this->refreshState();
        $this->currentTime = now()->format('H:i');
    }

    public function refreshState()
    {
        $this->attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if ($this->attendance) {
            $this->currentBreak = $this->attendance->breaks()
                ->whereNull('ended_at')
                ->first();
        }
    }

    public function checkIn()
    {
        $user = Auth::user();
        $now = now();

        // Check if shift is configured
        if ($user->shift_start) {
            $shiftStart = Carbon::parse($user->shift_start);
            $shiftEnd = Carbon::parse($user->shift_end);

            // Handle night shift (shift end is earlier than shift start, spans midnight)
            $isNightShift = $shiftEnd->lt($shiftStart);

            $todayShiftStart = $shiftStart->clone()->setDate($now->year, $now->month, $now->day);

            // For night shifts, if current time is very early (before shift start hour),
            // it might be continuation of previous day's shift
            if ($isNightShift && $now->hour < $shiftStart->hour) {
                // Employee is checking in early morning - should be from previous day's shift
                $todayShiftStart->subDay();
            }

            // Check if it's too early to check in (before shift start)
            if ($now->lt($todayShiftStart)) {
                session()->flash('error', 'You cannot check in before your shift starts at ' . $shiftStart->format('h:i A'));
                return;
            }
        }

        // Validation: Verify Geo if needed (skipped for now, assumed frontend sends it)

        $this->attendance = Attendance::create([
            'user_id' => Auth::id(),
            'date' => today(),
            'check_in' => now(),
            'status' => 'present',
        ]);

        $this->attendance->load('user');

        // Apply late penalty if check-in is after shift start + grace
        app(AttendancePenaltyService::class)->applyLatePenalty($this->attendance);

        $this->attendance->refresh();

        $this->refreshState();

        session()->flash('success', 'Checked in successfully at ' . now()->format('H:i'));
    }

    public function checkOut()
    {
        if (!$this->attendance) return;

        // Auto-close any open break
        if ($this->currentBreak) {
            $this->endBreak();
        }

        $this->attendance->update([
            'check_out' => now(),
            'work_duration' => now()->diffInMinutes($this->attendance->check_in),
        ]);

        session()->flash('success', 'Checked out successfully. Have a great evening!');
        $this->refreshState();
    }

    public function startBreak($type = 'lunch')
    {
        if (!$this->attendance || $this->currentBreak) return;

        $this->attendance->update(['status' => 'on_break']);

        AttendanceBreak::create([
            'attendance_id' => $this->attendance->id,
            'started_at' => now(),
            'type' => $type,
        ]);

        session()->flash('success', 'Break started.');
        $this->refreshState();
    }

    public function endBreak()
    {
        if (!$this->currentBreak) return;

        $this->currentBreak->update(['ended_at' => now()]);

        $this->attendance->refresh()->load('breaks', 'user');

        $totalBreakMinutes = $this->attendance->total_break_duration;

        app(AttendancePenaltyService::class)
            ->applyBreakOveragePenalty($this->attendance, $totalBreakMinutes);

        $this->attendance->update(['status' => 'present']);

        session()->flash('success', 'Welcome back!');
        $this->refreshState();
    }

    public function render()
    {
        return view('livewire.employee.employee-punch-pad');
    }
}
