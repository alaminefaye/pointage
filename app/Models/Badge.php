<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Badge extends Model
{
    protected $fillable = [
        'badge_number',
        'qr_code',
        'employee_id',
        'notes',
        'is_active',
        'issued_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    /**
     * Get the employee that owns this badge.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if badge is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if badge is valid (active and not expired).
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
