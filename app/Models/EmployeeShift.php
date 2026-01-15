<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeShift extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'break_allowance_minutes',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_shift_id');
    }

    public function monthlyShifts(): HasMany
    {
        return $this->hasMany(EmployeeMonthlyShift::class, 'employee_shift_id');
    }
}
