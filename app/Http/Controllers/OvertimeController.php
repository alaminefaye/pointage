<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRecord;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class OvertimeController extends Controller
{
    /**
     * Display a listing of overtime records.
     */
    public function index(Request $request)
    {
        $query = OvertimeRecord::with(['employee', 'attendanceRecord'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by employee if provided
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $overtimeRecords = $query->paginate(20);
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();

        return view('overtime.index', compact('overtimeRecords', 'employees'));
    }

    /**
     * Show the form for creating a new overtime record.
     */
    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        return view('overtime.create', compact('employees'));
    }

    /**
     * Store a newly created overtime record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0|max:24',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if a manual record already exists for this employee and date
        $existingManual = OvertimeRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', 'manual')
            ->first();

        if ($existingManual) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Les heures supplémentaires manuelles pour cet employé ont déjà été définies pour la date {$validated['date']}. Veuillez modifier l'enregistrement existant.");
        }

        // Si un enregistrement automatique existe, on le supprime pour le remplacer par le manuel
        $existingAuto = OvertimeRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', 'auto')
            ->first();

        if ($existingAuto) {
            $existingAuto->delete();
        }

        OvertimeRecord::create([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'type' => 'manual',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('overtime.index')
            ->with('success', 'Heures supplémentaires enregistrées avec succès.');
    }

    /**
     * Show the form for editing the specified overtime record.
     */
    public function edit(OvertimeRecord $overtime)
    {
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        return view('overtime.edit', compact('overtime', 'employees'));
    }

    /**
     * Update the specified overtime record.
     */
    public function update(Request $request, OvertimeRecord $overtime)
    {
        // Only allow editing manual records
        if ($overtime->type !== 'manual') {
            return redirect()->route('overtime.index')
                ->with('error', 'Les heures supplémentaires automatiques ne peuvent pas être modifiées.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0|max:24',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if another manual record exists for this employee and date (excluding current)
        $existing = OvertimeRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', 'manual')
            ->where('id', '!=', $overtime->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Les heures supplémentaires pour cet employé ont déjà été définies pour la date {$validated['date']}.");
        }

        $overtime->update($validated);

        return redirect()->route('overtime.index')
            ->with('success', 'Heures supplémentaires mises à jour avec succès.');
    }

    /**
     * Remove the specified overtime record.
     */
    public function destroy(OvertimeRecord $overtime)
    {
        // Only allow deleting manual records
        if ($overtime->type !== 'manual') {
            return redirect()->route('overtime.index')
                ->with('error', 'Les heures supplémentaires automatiques ne peuvent pas être supprimées.');
        }

        $overtime->delete();

        return redirect()->route('overtime.index')
            ->with('success', 'Heures supplémentaires supprimées avec succès.');
    }

    /**
     * Display monthly accounting/summary of overtime hours.
     */
    public function accounting(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $employeeId = $request->get('employee_id');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = OvertimeRecord::with('employee')
            ->whereBetween('date', [$startDate, $endDate]);

        // Filter by employee if provided
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $records = $query->get();

        // Calculate totals by employee
        $summaryByEmployee = $records->groupBy('employee_id')->map(function ($employeeRecords) {
            return [
                'employee' => $employeeRecords->first()->employee,
                'total_hours' => $employeeRecords->sum('hours'),
                'manual_hours' => $employeeRecords->where('type', 'manual')->sum('hours'),
                'auto_hours' => $employeeRecords->where('type', 'auto')->sum('hours'),
                'count' => $employeeRecords->count(),
            ];
        })->sortByDesc('total_hours');

        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $totalHours = $records->sum('hours');

        return view('overtime.accounting', compact(
            'summaryByEmployee',
            'employees',
            'year',
            'month',
            'employeeId',
            'startDate',
            'endDate',
            'totalHours'
        ));
    }

    /**
     * Generate monthly report for overtime.
     */
    public function report(Request $request)
    {
        // Si aucun paramètre n'est fourni, utiliser les valeurs par défaut (mois actuel)
        if (!$request->hasAny(['year', 'month', 'employee_id'])) {
            $year = date('Y');
            $month = date('m');
            $employeeId = null;
        } else {
            $validated = $request->validate([
                'employee_id' => 'nullable|exists:employees,id',
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
            ]);

            $year = $validated['year'];
            $month = $validated['month'];
            $employeeId = $validated['employee_id'] ?? null;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = OvertimeRecord::with(['employee', 'attendanceRecord'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
            $employee = Employee::findOrFail($employeeId);
        } else {
            $employee = null;
        }

        $records = $query->get();

        // Calculate summary
        $summary = [
            'total_hours' => $records->sum('hours'),
            'manual_hours' => $records->where('type', 'manual')->sum('hours'),
            'auto_hours' => $records->where('type', 'auto')->sum('hours'),
            'total_records' => $records->count(),
        ];

        // If specific employee, add employee-specific summary
        if ($employee) {
            $summary['employee_name'] = $employee->full_name;
            $summary['employee_code'] = $employee->employee_code;
        }

        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();

        return view('overtime.report', compact(
            'records',
            'summary',
            'employee',
            'employees',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export overtime report to PDF.
     */
    public function exportPdf(Request $request)
    {
        // Si aucun paramètre n'est fourni, utiliser les valeurs par défaut (mois actuel)
        if (!$request->hasAny(['year', 'month', 'employee_id'])) {
            $year = date('Y');
            $month = date('m');
            $employeeId = null;
        } else {
            $validated = $request->validate([
                'employee_id' => 'nullable|exists:employees,id',
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
            ]);

            $year = $validated['year'];
            $month = $validated['month'];
            $employeeId = $validated['employee_id'] ?? null;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = OvertimeRecord::with(['employee', 'attendanceRecord'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
            $employee = Employee::findOrFail($employeeId);
        } else {
            $employee = null;
        }

        $records = $query->get();

        // Calculate summary
        $summary = [
            'total_hours' => $records->sum('hours'),
            'manual_hours' => $records->where('type', 'manual')->sum('hours'),
            'auto_hours' => $records->where('type', 'auto')->sum('hours'),
            'total_records' => $records->count(),
        ];

        // If specific employee, add employee-specific summary
        if ($employee) {
            $summary['employee_name'] = $employee->full_name;
            $summary['employee_code'] = $employee->employee_code;
        }

        // Generate PDF
        $pdf = Pdf::loadView('overtime.report-pdf', compact(
            'records',
            'summary',
            'employee',
            'year',
            'month',
            'startDate',
            'endDate'
        ));

        // Generate filename
        $monthName = Carbon::create($year, $month, 1)->locale('fr')->monthName;
        $filename = 'Rapport-Heures-Supplementaires';
        if ($employee) {
            $filename .= '-' . str_replace(' ', '-', $employee->full_name);
        }
        $filename .= '-' . $monthName . '-' . $year . '.pdf';

        return $pdf->download($filename);
    }
}
