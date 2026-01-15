<?php

namespace App\Livewire\Employee;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\EmployeeShift;
use App\Services\AttendancePenaltyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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
            ->whereNull('check_out')
            ->latest('check_in')
            ->with('shift')
            ->first();

        if ($this->attendance) {
            $this->currentBreak = $this->attendance->breaks()
                ->whereNull('ended_at')
                ->first();
        }
    }

    private function checkInBlockReason(): ?string
    {
        $user = Auth::user();
        $now = now();
        $this->ensureShifts($user);

        if ($user->shifts->isEmpty()) {
            return 'Your shifts are not configured. Contact your administrator.';
        }

        $currentShift = $this->resolveShiftForMoment($user->shifts, $now);

        if ($currentShift) {
            return null;
        }

        $nextWindow = $this->nextShiftWindow($user->shifts, $now);

        if ($nextWindow) {
            return 'You can check in at '.$nextWindow['start']->format('h:i A').' for your next shift.';
        }

        return 'No shift is scheduled for the current time.';
    }

    private function resolveShiftEnd(): ?Carbon
    {
        $user = Auth::user();
        if (! $this->attendance || ! $this->attendance->shift) {
            return null;
        }

        $window = $this->buildShiftWindow(
            $this->attendance->shift,
            Carbon::parse($this->attendance->date)
        );

        return $window['end'];
    }

    private function ensureShifts($user): void
    {
        $user->loadMissing('shifts');

        if ($user->shifts->isNotEmpty()) {
            return;
        }

        if ($user->shift_start && $user->shift_end) {
            $legacyDays = $user->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];

            foreach ($legacyDays as $day) {
                $user->shifts()->create([
                    'day_of_week' => $day,
                    'start_time' => $user->shift_start,
                    'end_time' => $user->shift_end,
                    'grace_period_minutes' => $user->grace_period_minutes ?? 0,
                    'break_allowance_minutes' => $user->break_allowance_minutes ?? 0,
                ]);
            }

            $user->refresh()->loadMissing('shifts');
        }
    }

    private function buildShiftWindow(EmployeeShift $shift, Carbon $anchorDate): array
    {
        $shiftStart = Carbon::parse($shift->start_time)->setDate(
            $anchorDate->year,
            $anchorDate->month,
            $anchorDate->day
        );

        $shiftEnd = Carbon::parse($shift->end_time)->setDate(
            $anchorDate->year,
            $anchorDate->month,
            $anchorDate->day
        );

        if ($shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        return [
            'start' => $shiftStart,
            'end' => $shiftEnd,
        ];
    }

    private function resolveShiftForMoment($shifts, Carbon $moment): ?EmployeeShift
    {
        $user = Auth::user();

        // Check for monthly shift override first
        $monthlyOverride = \App\Models\EmployeeMonthlyShift::where('user_id', $user->id)
            ->where('date', $moment->toDateString())
            ->first();

        if ($monthlyOverride) {
            return $monthlyOverride->shift;
        }

        $dayKey = strtolower($moment->format('D'));
        $previousDayKey = strtolower($moment->copy()->subDay()->format('D'));

        $matching = null;
        $matchingStart = null;

        foreach ($shifts as $shift) {
            if (! $shift->is_active) {
                continue;
            }

            if ($shift->day_of_week !== $dayKey && $shift->day_of_week !== $previousDayKey) {
                continue;
            }

            $anchorDate = $shift->day_of_week === $dayKey ? $moment->copy() : $moment->copy()->subDay();
            $window = $this->buildShiftWindow($shift, $anchorDate);

            if ($moment->between($window['start'], $window['end'])) {
                if (! $matching || $window['start']->lt($matchingStart)) {
                    $matching = $shift;
                    $matchingStart = $window['start'];
                }
            }
        }

        return $matching;
    }

    private function nextShiftWindow($shifts, Carbon $moment): ?array
    {
        $next = null;
        $shifts = collect($shifts)->where('is_active', true);

        for ($i = 0; $i <= 6; $i++) {
            $candidateDate = $moment->copy()->addDays($i);
            $dayKey = strtolower($candidateDate->format('D'));

            foreach ($shifts as $shift) {
                if ($shift->day_of_week !== $dayKey) {
                    continue;
                }

                $window = $this->buildShiftWindow($shift, $candidateDate);

                if ($window['start']->lte($moment)) {
                    continue;
                }

                if (! $next || $window['start']->lt($next['start'])) {
                    $next = [
                        'shift' => $shift,
                        'start' => $window['start'],
                        'end' => $window['end'],
                    ];
                }
            }

            if ($next) {
                break;
            }
        }

        return $next;
    }

    public function checkIn()
    {
        $user = Auth::user();
        $now = now();

        $this->ensureShifts($user);

        if ($user->shifts->isEmpty()) {
            session()->flash('error', 'Your shifts are not configured. Contact your administrator.');

            return;
        }

        $shift = $this->resolveShiftForMoment($user->shifts, $now);

        if (! $shift) {
            $reason = $this->checkInBlockReason();
            session()->flash('error', $reason ?? 'No shift is scheduled for this time.');

            return;
        }

        $anchorDate = strtolower($now->format('D')) === $shift->day_of_week ? $now->copy() : $now->copy()->subDay();
        $window = $this->buildShiftWindow($shift, $anchorDate);
        $attendanceDate = $window['start']->toDateString();

        $existing = Attendance::where('user_id', $user->id)
            ->where('employee_shift_id', $shift->id)
            ->where('date', $attendanceDate)
            ->whereNull('check_out')
            ->first();

        if ($existing) {
            $this->attendance = $existing;
            session()->flash('error', 'You are already checked in for this shift.');

            return;
        }

        $this->attendance = Attendance::create([
            'user_id' => Auth::id(),
            'employee_shift_id' => $shift->id,
            'date' => $attendanceDate,
            'check_in' => $now,
            'status' => 'present',
        ]);

        $this->attendance->load('user', 'shift');

        app(AttendancePenaltyService::class)->applyLatePenalty($this->attendance);

        $this->attendance->refresh();

        $this->refreshState();

        session()->flash('success', 'Checked in successfully at '.$now->format('H:i'));
    }

    public function checkOut()
    {
        if (! $this->attendance) {
            return;
        }

        $shiftEndTime = $this->resolveShiftEnd();

        if (! $shiftEndTime) {
            session()->flash('error', 'Your shift times are not configured. Contact your administrator.');

            return;
        }

        if (now()->lt($shiftEndTime)) {
            session()->flash('error', 'You cannot check out before your shift ends at '.$shiftEndTime->format('h:i A').'.');

            return;
        }

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
        if (! $this->attendance || $this->currentBreak) {
            return;
        }

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
        if (! $this->currentBreak) {
            return;
        }

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
        return view('livewire.employee.employee-punch-pad', [
            'checkInBlockReason' => $this->checkInBlockReason(),
        ]);
    }
}
