<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeRestDay;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\Alert;
use Carbon\Carbon;

class EmployeeRestDayController extends Controller
{
    /**
     * Store a rest day for an employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        // Vérifier si un jour de repos existe déjà pour cette date
        $existing = EmployeeRestDay::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Ce jour est déjà marqué comme jour de repos pour cet employé.');
        }

        EmployeeRestDay::create($validated);

        // Si un enregistrement d'absence existe pour cette date, le retirer
        $attendance = AttendanceRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($attendance && $attendance->is_absent) {
            $attendance->update(['is_absent' => false]);
        }

        // Supprimer les alertes d'absence pour cette date et cet employé
        // Les alertes d'absence ont la date dans metadata['date']
        $dateFormatted = Carbon::parse($validated['date'])->format('Y-m-d');
        $absenceAlerts = Alert::where('employee_id', $validated['employee_id'])
            ->where('type', 'absence')
            ->get();
        
        foreach ($absenceAlerts as $alert) {
            if (isset($alert->metadata['date']) && $alert->metadata['date'] === $dateFormatted) {
                $alert->delete();
            }
        }

        return redirect()->back()
            ->with('success', 'Jour de repos ajouté avec succès. L\'absence a été retirée si elle existait.');
    }

    /**
     * Remove a rest day for an employee.
     */
    public function destroy(EmployeeRestDay $restDay)
    {
        $restDay->delete();

        return redirect()->back()
            ->with('success', 'Jour de repos supprimé avec succès.');
    }

    /**
     * Show rest days for an employee.
     */
    public function index(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $query = EmployeeRestDay::with('employee')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'desc');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $restDays = $query->paginate(20);
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();

        return view('rest-days.index', compact('restDays', 'employees', 'employeeId', 'dateFrom', 'dateTo'));
    }
}
