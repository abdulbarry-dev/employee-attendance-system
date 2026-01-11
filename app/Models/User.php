<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\EmployeePenalty;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'is_banned',
        'monthly_salary',
        'shift_start',
        'shift_end',
        'grace_period_minutes',
        'break_allowance_minutes',
        'working_days',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'monthly_salary' => 'decimal:2',
            'shift_start' => 'datetime:H:i',
            'shift_end' => 'datetime:H:i',
            'grace_period_minutes' => 'integer',
            'break_allowance_minutes' => 'integer',
            'working_days' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's full name from first and last name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->name;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is an employee
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function penalties()
    {
        return $this->hasMany(EmployeePenalty::class);
    }
}
