<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    protected $fillable = [
        'site_id',
        'code',
        'expires_at',
        'is_used',
        'used_by_employee_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_used' => 'boolean',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the site that owns the QR code.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the employee who used the QR code.
     */
    public function usedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'used_by_employee_id');
    }

    /**
     * Check if QR code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if QR code is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }
}
