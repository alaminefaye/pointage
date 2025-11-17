<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\Alert;
use App\Models\OvertimeRecord;
use Carbon\Carbon;

class AttendanceCalculationService
{
    /**
     * Calculate attendance for a specific record.
     */
    public function calculateAttendance(AttendanceRecord $record): void
    {
        $employee = $record->employee;
        
        if (!$record->check_in_time || !$record->check_out_time) {
            return;
        }

        // Convertir les heures en format string si nécessaire
        $checkInTime = $record->check_in_time instanceof \DateTime 
            ? $record->check_in_time->format('H:i:s') 
            : (string) $record->check_in_time;
        $checkOutTime = $record->check_out_time instanceof \DateTime 
            ? $record->check_out_time->format('H:i:s') 
            : (string) $record->check_out_time;
        
        $checkIn = Carbon::parse($record->date->format('Y-m-d') . ' ' . $checkInTime);
        $checkOut = Carbon::parse($record->date->format('Y-m-d') . ' ' . $checkOutTime);
        
        // Si le check_out est avant le check_in (ex: 17h -> 01h), 
        // cela signifie que le check_out est le lendemain
        if ($checkOut->lt($checkIn)) {
            // Ajouter 24 heures au check_out pour obtenir le bon moment
            $checkOut->addDay();
        }
        
        // Calculate total minutes worked
        $totalMinutes = $checkIn->diffInMinutes($checkOut);
        
        // Calculate overtime (work beyond standard hours)
        // Les heures supplémentaires sont calculées en fonction du nombre d'heures travaillées
        // vs le nombre d'heures standard par jour (pas d'heures de début/fin fixes)
        $standardMinutes = $employee->standard_hours_per_day * 60;
        $overtimeMinutes = max(0, $totalMinutes - $standardMinutes);
        
        // Plus de calcul de retard car les heures de début/fin ne sont plus fixes
        $record->update([
            'total_minutes' => $totalMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'is_late' => false,
            'late_minutes' => 0,
        ]);

        // Vérifier si l'employé n'a pas fait ses heures standard (moins que requis)
        $totalHours = $totalMinutes / 60;
        $standardHours = $employee->standard_hours_per_day;
        
        if ($totalHours < $standardHours) {
            // Créer une alerte pour heures insuffisantes
            $missingHours = $standardHours - $totalHours;
            $this->createInsufficientHoursAlert($employee, $record, $totalHours, $standardHours, $missingHours);
        }

        // Create alert if overtime threshold exceeded
        // Use employee's threshold if set, otherwise use global threshold
        $overtimeThreshold = $employee->overtime_threshold_hours 
            ?? (float) \App\Models\AttendanceSetting::getValue(null, 'overtime_threshold_hours', 10);
        
        $overtimeHours = $overtimeMinutes / 60;
        
        if ($overtimeHours > $overtimeThreshold) {
            $this->createOvertimeAlert($employee, $record, $overtimeMinutes);
            
            // Enregistrer automatiquement les heures supplémentaires dans la base de données
            // Ne pas créer si un enregistrement manuel existe déjà (priorité au manuel)
            $existingManual = OvertimeRecord::where('employee_id', $employee->id)
                ->where('date', $record->date)
                ->where('type', 'manual')
                ->first();
            
            if (!$existingManual) {
                // Utiliser updateOrCreate pour éviter les doublons même en cas d'appels multiples
                // La contrainte unique dans la base de données empêche aussi les doublons
                OvertimeRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $record->date,
                        'type' => 'auto',
                    ],
                    [
                        'hours' => round($overtimeHours, 2),
                        'attendance_record_id' => $record->id,
                    ]
                );
            }
        }
    }

    /**
     * Detect absences for all employees for a specific date.
     * Note: Vérifie si c'est un jour de repos avant de marquer comme absent.
     */
    public function detectAbsences(Carbon $date): void
    {
        // Charger les employés avec leurs jours de repos pour optimiser les requêtes
        $employees = Employee::where('is_active', true)
            ->with(['restDays' => function ($query) use ($date) {
                $query->where('date', $date->format('Y-m-d'));
            }])
            ->get();

        foreach ($employees as $employee) {
            // Vérifier si c'est un jour de repos pour cet employé
            // Utiliser la collection chargée plutôt qu'une nouvelle requête
            if ($employee->restDays->isNotEmpty()) {
                continue; // Skip rest days
            }

            // Check if employee has attendance record for this date
            $attendance = AttendanceRecord::where('employee_id', $employee->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                // Mark as absent
                if (!$attendance) {
                    $attendance = AttendanceRecord::create([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'is_absent' => true,
                    ]);
                } else {
                    $attendance->update(['is_absent' => true]);
                }

                // Create absence alert
                $this->createAbsenceAlert($employee, $date);
            }
        }
    }

    /**
     * Calculate monthly summary for an employee.
     */
    public function calculateMonthlySummary(int $employeeId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $records = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalMinutes = $records->sum('total_minutes');
        $totalOvertimeMinutes = $records->sum('overtime_minutes');
        $totalLateMinutes = $records->sum('late_minutes');
        $absences = $records->where('is_absent', true)->count();
        $lates = $records->where('is_late', true)->count();

        return [
            'total_hours' => round($totalMinutes / 60, 2),
            'total_overtime_hours' => round($totalOvertimeMinutes / 60, 2),
            'total_late_hours' => round($totalLateMinutes / 60, 2),
            'absences' => $absences,
            'lates' => $lates,
            'working_days' => $records->where('is_absent', false)->count(),
        ];
    }

    /**
     * Create late alert.
     */
    private function createLateAlert(Employee $employee, AttendanceRecord $record, int $lateMinutes): void
    {
        Alert::create([
            'employee_id' => $employee->id,
            'type' => 'late',
            'title' => 'Retard détecté',
            'message' => "L'employé {$employee->full_name} a été en retard de {$lateMinutes} minutes le {$record->date->format('d/m/Y')}.",
            'severity' => 'warning',
            'metadata' => [
                'attendance_record_id' => $record->id,
                'late_minutes' => $lateMinutes,
            ],
        ]);
    }

    /**
     * Create overtime alert.
     */
    private function createOvertimeAlert(Employee $employee, AttendanceRecord $record, int $overtimeMinutes): void
    {
        Alert::create([
            'employee_id' => $employee->id,
            'type' => 'overtime',
            'title' => 'Heures supplémentaires élevées',
            'message' => "L'employé {$employee->full_name} a effectué " . round($overtimeMinutes / 60, 2) . " heures supplémentaires le {$record->date->format('d/m/Y')}.",
            'severity' => 'info',
            'metadata' => [
                'attendance_record_id' => $record->id,
                'overtime_minutes' => $overtimeMinutes,
            ],
        ]);
    }

    /**
     * Create absence alert.
     */
    private function createAbsenceAlert(Employee $employee, Carbon $date): void
    {
        Alert::create([
            'employee_id' => $employee->id,
            'type' => 'absence',
            'title' => 'Absence non justifiée',
            'message' => "L'employé {$employee->full_name} était absent le {$date->format('d/m/Y')} sans pointage.",
            'severity' => 'error',
            'metadata' => [
                'date' => $date->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Create insufficient hours alert.
     */
    private function createInsufficientHoursAlert(Employee $employee, AttendanceRecord $record, float $totalHours, float $standardHours, float $missingHours): void
    {
        Alert::create([
            'employee_id' => $employee->id,
            'type' => 'system',
            'title' => 'Heures de travail insuffisantes',
            'message' => "L'employé {$employee->full_name} a travaillé seulement " . round($totalHours, 2) . "h le {$record->date->format('d/m/Y')} au lieu des {$standardHours}h requises. Il manque " . round($missingHours, 2) . "h de travail.",
            'severity' => 'warning',
            'metadata' => [
                'attendance_record_id' => $record->id,
                'total_hours' => $totalHours,
                'standard_hours' => $standardHours,
                'missing_hours' => $missingHours,
            ],
        ]);
    }
}

