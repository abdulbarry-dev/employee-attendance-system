<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePenalty extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'type',
        'period_start',
        'period_end',
        'occurred_on',
        'minutes_late',
        'break_overage_minutes',
        'penalty_steps',
        'penalty_amount',
        'reason',
        'notified_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'occurred_on' => 'date',
        'notified_at' => 'datetime',
        'minutes_late' => 'integer',
        'break_overage_minutes' => 'integer',
        'penalty_steps' => 'integer',
        'penalty_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
