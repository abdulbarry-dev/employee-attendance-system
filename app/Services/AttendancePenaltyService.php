<?php

namespace App\Services;

use App\Jobs\SendPenaltyNotification;
use App\Models\Attendance;
use App\Models\EmployeePenalty;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendancePenaltyService
{
    private const PENALTY_STEP_MINUTES = 5; // each 5 minutes

    private const PENALTY_STEP_PERCENT = 0.05; // 5% of daily salary per step

    public function applyLatePenalty(Attendance $attendance): ?EmployeePenalty
    {
        $user = $attendance->user;
        $date = Carbon::parse($attendance->date);
        $checkIn = Carbon::parse($attendance->check_in);

        $shift = $attendance->shift;
        $shiftStartValue = $shift?->start_time ?? $user->shift_start;
        $shiftEndValue = $shift?->end_time ?? $user->shift_end;

        if (! $shiftStartValue || ! $shiftEndValue) {
            return null; // no shift configured, skip penalties
        }

        $shiftStart = Carbon::parse($shiftStartValue);
        $shiftEnd = Carbon::parse($shiftEndValue);

        // Handle night shift (shift end is earlier than shift start, spans midnight)
        $isNightShift = $shiftEnd->lt($shiftStart);

        $allowedStart = $shiftStart->clone()->setDate($date->year, $date->month, $date->day);
        $allowedStart->addMinutes($shift->grace_period_minutes ?? $user->grace_period_minutes ?? 0);

        // If night shift and check-in is very early morning, use previous day's shift time
        if ($isNightShift && $checkIn->hour < $shiftStart->hour) {
            $allowedStart->subDay();
        }

        if ($checkIn->lte($allowedStart)) {
            return null; // on time
        }

        $lateMinutes = $checkIn->diffInMinutes($allowedStart);
        $steps = (int) ceil($lateMinutes / self::PENALTY_STEP_MINUTES);
        $penaltyAmount = $this->calculatePenaltyAmount($user, $date, $steps);

        $attendance->update(['status' => 'late']);

        $penalty = EmployeePenalty::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'type' => 'late',
            'occurred_on' => $date,
            'minutes_late' => $lateMinutes,
            'penalty_steps' => $steps,
            'penalty_amount' => $penaltyAmount,
            'reason' => 'Late check-in',
            'period_start' => $date->copy()->startOfMonth(),
            'period_end' => $date->copy()->endOfMonth(),
        ]);

        $this->notifyPenalty($user, $penalty);

        return $penalty;
    }

    public function applyBreakOveragePenalty(Attendance $attendance, int $breakMinutes): ?EmployeePenalty
    {
        $user = $attendance->user;
        $date = Carbon::parse($attendance->date);
        $allowance = $attendance->shift?->break_allowance_minutes ?? $user->break_allowance_minutes ?? 0;

        if ($allowance === 0 || $breakMinutes <= $allowance) {
            return null; // no allowance set or within allowance
        }

        $overMinutes = $breakMinutes - $allowance;
        $steps = (int) ceil($overMinutes / self::PENALTY_STEP_MINUTES);
        $penaltyAmount = $this->calculatePenaltyAmount($user, $date, $steps);

        $penalty = EmployeePenalty::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'type' => 'break_overage',
            'occurred_on' => $date,
            'break_overage_minutes' => $overMinutes,
            'penalty_steps' => $steps,
            'penalty_amount' => $penaltyAmount,
            'reason' => 'Overlong break',
            'period_start' => $date->copy()->startOfMonth(),
            'period_end' => $date->copy()->endOfMonth(),
        ]);

        $this->notifyPenalty($user, $penalty);

        return $penalty;
    }

    private function calculatePenaltyAmount(User $user, Carbon $date, int $steps): float
    {
        if ($steps <= 0 || ! $user->monthly_salary) {
            return 0.0;
        }

        $workingDaysInPeriod = $this->countWorkingDaysInMonth($user, $date);
        if ($workingDaysInPeriod === 0) {
            return 0.0;
        }

        $dailySalary = (float) $user->monthly_salary / $workingDaysInPeriod;
        $penaltyPerStep = $dailySalary * self::PENALTY_STEP_PERCENT;

        return round($penaltyPerStep * $steps, 2);
    }

    private function countWorkingDaysInMonth(User $user, Carbon $date): int
    {
        $workingDays = $user->working_days ?? [];
        if (empty($workingDays)) {
            $workingDays = ['mon', 'tue', 'wed', 'thu', 'fri'];
        }

        $period = CarbonPeriod::create($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
        $count = 0;

        foreach ($period as $day) {
            $key = strtolower($day->format('D')); // sun, mon, tue, wed, thu, fri, sat
            if (in_array($key, $workingDays, true)) {
                $count++;
            }
        }

        return $count;
    }

    private function notifyPenalty(User $user, EmployeePenalty $penalty): void
    {
        SendPenaltyNotification::dispatch($user, $penalty);
    }
}
