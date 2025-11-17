<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'department_id',
        'position',
        'standard_start_time',
        'standard_end_time',
        'standard_hours_per_day',
        'overtime_threshold_hours',
        'rest_days',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'rest_days' => 'array',
            'is_active' => 'boolean',
            'overtime_threshold_hours' => 'decimal:2',
        ];
    }

    /**
     * Get the department that owns the employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the alerts for the employee.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get the overtime records for the employee.
     */
    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecord::class);
    }

    /**
     * Get the rest days for the employee.
     */
    public function restDays(): HasMany
    {
        return $this->hasMany(EmployeeRestDay::class);
    }

    /**
     * Check if a specific date is a rest day for this employee.
     */
    public function isRestDay(Carbon $date): bool
    {
        return $this->restDays()
            ->where('date', $date->format('Y-m-d'))
            ->exists();
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
