<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'radius',
        'is_active',
        'static_qr_code',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'radius' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the QR codes for the site.
     */
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    /**
     * Get the attendance records for the site.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the attendance settings for the site.
     */
    public function attendanceSettings(): HasMany
    {
        return $this->hasMany(AttendanceSetting::class);
    }
}
