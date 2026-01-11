<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Calculate total break duration in minutes
     */
    public function getTotalBreakDurationAttribute()
    {
        return $this->breaks
            ->whereNotNull('ended_at')
            ->sum(function ($break) {
                return $break->ended_at->diffInMinutes($break->started_at);
            });
    }
}
