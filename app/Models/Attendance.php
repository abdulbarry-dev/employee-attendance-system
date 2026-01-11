<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeePenalty;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
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
        if ($this->breaks->isEmpty()) {
            return 0;
        }

        return $this->breaks
            ->filter(function ($break) {
                return $break->ended_at !== null;
            })
            ->sum(function ($break) {
                return $break->ended_at->diffInMinutes($break->started_at);
            });
    }

    /**
     * Calculate actual work duration (checkout - checkin - breaks) in minutes
     */
    public function getActualWorkDurationAttribute()
    {
        if (!$this->check_out) {
            return 0;
        }

        $totalMinutes = $this->check_out->diffInMinutes($this->check_in);
        $breakMinutes = $this->total_break_duration;

        return max(0, $totalMinutes - $breakMinutes);
    }
}
