<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'site_id',
        'date',
        'check_in_time',
        'check_out_time',
        'latitude',
        'longitude',
        'is_in_zone',
        'total_minutes',
        'overtime_minutes',
        'is_absent',
        'is_late',
        'late_minutes',
        'notes',
        'qr_code_used',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_time' => 'datetime:H:i',
            'check_out_time' => 'datetime:H:i',
            'is_in_zone' => 'boolean',
            'is_absent' => 'boolean',
            'is_late' => 'boolean',
        ];
    }

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the site for the attendance record.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Calculate total hours worked.
     */
    public function getTotalHoursAttribute(): float
    {
        return round($this->total_minutes / 60, 2);
    }

    /**
     * Calculate overtime hours.
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }
}
