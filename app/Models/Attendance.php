<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'employee_shift_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'work_duration',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    protected $appends = ['total_break_duration', 'actual_work_duration'];

    public function shift()
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function penalties()
    {
        return $this->hasMany(EmployeePenalty::class);
    }

    /**
     * Calculate total break duration in minutes
     */
    public function getTotalBreakDurationAttribute()
    {
        $breakDuration = 0;

        foreach ($this->breaks as $break) {
            if ($break->ended_at && $break->started_at) {
                $breakDuration += abs($break->ended_at->diffInMinutes($break->started_at));
            }
        }

        return $breakDuration;
    }

    /**
     * Calculate actual work duration (checkout - checkin - breaks) in minutes
     */
    public function getActualWorkDurationAttribute()
    {
        if (! $this->check_out || ! $this->check_in) {
            return 0;
        }

        $totalMinutes = abs($this->check_out->diffInMinutes($this->check_in));
        $breakMinutes = $this->total_break_duration;

        return max(0, $totalMinutes - $breakMinutes);
    }
}
