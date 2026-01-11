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

    private function checkInBlockReason(): ?string
    {
        $user = Auth::user();
        $now = now();

        if (!$user->shift_start || !$user->shift_end) {
            return 'Your shift times are not configured. Contact your administrator.';
        }

        $workingDays = $user->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
        $workingDays = is_array($workingDays) ? array_map('strtolower', $workingDays) : ['mon', 'tue', 'wed', 'thu', 'fri'];

        $shiftStart = Carbon::parse($user->shift_start);
        $shiftEnd = Carbon::parse($user->shift_end);

        $shiftDate = $now->copy();
        $isNightShift = $shiftEnd->lt($shiftStart);

        $todayShiftStart = $shiftStart->clone()->setDate($now->year, $now->month, $now->day);

        if ($isNightShift && $now->hour < $shiftStart->hour) {
            $todayShiftStart->subDay();
            $shiftDate = $shiftDate->subDay();
        }

        $dayKey = strtolower($shiftDate->format('D'));

        if (!in_array($dayKey, $workingDays, true)) {
            return 'You cannot check in today because this is not one of your scheduled working days.';
        }

        if ($now->lt($todayShiftStart)) {
            return 'You can check in at ' . $shiftStart->format('h:i A') . ' once your shift starts.';
        }

        return null;
    }

    private function resolveShiftEnd(): ?Carbon
    {
        $user = Auth::user();

        if (!$this->attendance || !$user->shift_start || !$user->shift_end) {
            return null;
        }

        $shiftStart = Carbon::parse($user->shift_start);
        $shiftEnd = Carbon::parse($user->shift_end);
        $checkInTime = $this->attendance->check_in ?? now();

        $shiftStartDateTime = $shiftStart->copy()->setDate(
            $checkInTime->year,
            $checkInTime->month,
            $checkInTime->day
        );

        $isNightShift = $shiftEnd->lt($shiftStart);

        if ($isNightShift && $checkInTime->hour < $shiftStart->hour) {
            $shiftStartDateTime->subDay();
        }

        $shiftEndDateTime = $shiftEnd->copy()->setDate(
            $shiftStartDateTime->year,
            $shiftStartDateTime->month,
            $shiftStartDateTime->day
        );

        if ($isNightShift) {
            $shiftEndDateTime->addDay();
        }

        return $shiftEndDateTime;
    }

    public function checkIn()
    {
        $user = Auth::user();
        $now = now();

        // Require shift times to be configured
        if (!$user->shift_start || !$user->shift_end) {
            session()->flash('error', 'Your shift times are not configured. Contact your administrator.');
            return;
        }

        // If not configured, assume standard weekdays to prevent weekend check-ins by default
        $workingDays = $user->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];

        $shiftStart = Carbon::parse($user->shift_start);
        $shiftEnd = Carbon::parse($user->shift_end);

        // Determine shift date (for night shifts, early morning belongs to previous day)
        $shiftDate = $now->copy();

        $isNightShift = $shiftEnd->lt($shiftStart);

        $todayShiftStart = $shiftStart->clone()->setDate($now->year, $now->month, $now->day);

        if ($isNightShift && $now->hour < $shiftStart->hour) {
            $todayShiftStart->subDay();
            $shiftDate = $shiftDate->subDay();
        }

        // Disallow check-in on non-working days (weekends or unscheduled days)
        // Ensure working_days is an array (handle if it's null or empty)
        $workingDays = is_array($workingDays) ? array_map('strtolower', $workingDays) : ['mon', 'tue', 'wed', 'thu', 'fri'];

        $dayKey = strtolower($shiftDate->format('D')); // Returns: 'Sun', 'Mon', 'Tue', etc. -> lowercase: 'sun', 'mon', 'tue', ...

        if (!in_array($dayKey, $workingDays, true)) {
            session()->flash('error', 'You cannot check in today because this is not one of your scheduled working days.');
            return;
        }

        // Check if it's too early to check in (before shift start)
        if ($now->lt($todayShiftStart)) {
            session()->flash('error', 'You cannot check in before your shift starts at ' . $shiftStart->format('h:i A'));
            return;
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

        $shiftEndTime = $this->resolveShiftEnd();

        if (!$shiftEndTime) {
            session()->flash('error', 'Your shift times are not configured. Contact your administrator.');
            return;
        }

        if (now()->lt($shiftEndTime)) {
            session()->flash('error', 'You cannot check out before your shift ends at ' . $shiftEndTime->format('h:i A') . '.');
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
        return view('livewire.employee.employee-punch-pad', [
            'checkInBlockReason' => $this->checkInBlockReason(),
        ]);
    }
}
