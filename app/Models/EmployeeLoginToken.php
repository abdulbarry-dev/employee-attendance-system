<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmployeeLoginToken extends Model
{
    protected $fillable = [
        'token',
        'user_id',
        'expires_at',
        'used_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generate(?int $userId = null, ?string $ipAddress = null): self
    {
        return self::create([
            'token' => Str::random(64),
            'user_id' => $userId,
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ipAddress,
        ]);
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && is_null($this->used_at);
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
