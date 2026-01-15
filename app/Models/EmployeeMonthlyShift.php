<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMonthlyShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'employee_shift_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the user that owns the monthly shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee shift associated with this date.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id');
    }

    /**
     * Check if this is an override (custom shift for this date).
     */
    public function isOverride(): bool
    {
        return $this->employee_shift_id !== null;
    }

    /**
     * Get the shift for a specific date, or null if no shift assigned.
     */
    public static function getShiftForDate(int $userId, \Carbon\Carbon $date): ?EmployeeShift
    {
        $monthlyShift = static::where('user_id', $userId)
            ->where('date', $date->toDateString())
            ->first();

        return $monthlyShift?->shift;
    }
}
