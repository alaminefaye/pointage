<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeRestDay;
use App\Services\AttendanceCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $calculationService;

    public function __construct(AttendanceCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Display reports index.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Generate monthly report.
     */
    public function monthly(Request $request)
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
            ], [
                'employee_id.exists' => 'L\'employé sélectionné n\'existe pas.',
                'year.required' => 'L\'année est obligatoire.',
                'year.integer' => 'L\'année doit être un nombre entier.',
                'year.min' => 'L\'année doit être supérieure ou égale à 2020.',
                'year.max' => 'L\'année doit être inférieure ou égale à 2100.',
                'month.required' => 'Le mois est obligatoire.',
                'month.integer' => 'Le mois doit être un nombre entier.',
                'month.min' => 'Le mois doit être entre 1 et 12.',
                'month.max' => 'Le mois doit être entre 1 et 12.',
            ]);

            $year = $validated['year'];
            $month = $validated['month'];
            $employeeId = $validated['employee_id'] ?? null;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = AttendanceRecord::with('employee.department', 'site')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('employee_id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
            $employee = Employee::findOrFail($employeeId);
        } else {
            $employee = null;
        }

        $records = $query->get();

        // Calculer les statistiques
        $summary = [
            'total_hours' => round($records->sum('total_minutes') / 60, 2),
            'total_overtime_hours' => round($records->sum('overtime_minutes') / 60, 2),
            'total_absences' => $records->where('is_absent', true)->count(),
            'total_working_days' => $records->where('is_absent', false)->whereNotNull('check_in_time')->count(),
            'total_records' => $records->count(),
        ];

        // Si un employé spécifique, calculer aussi les repos
        if ($employee) {
            $restDays = EmployeeRestDay::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            $summary['total_rest_days'] = $restDays;
            $summary['employee_name'] = $employee->full_name;
        } else {
            // Pour tous les employés, calculer les repos totaux
            $restDays = EmployeeRestDay::whereBetween('date', [$startDate, $endDate])
                ->distinct('employee_id', 'date')
                ->count();
            $summary['total_rest_days'] = $restDays;
        }

        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();

        return view('reports.monthly', compact(
            'employee',
            'summary',
            'records',
            'startDate',
            'endDate',
            'year',
            'month',
            'employees'
        ));
    }

    /**
     * Export report to PDF.
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

        $query = AttendanceRecord::with('employee.department', 'site')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('employee_id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
            $employee = Employee::findOrFail($employeeId);
        } else {
            $employee = null;
        }

        $records = $query->get();

        // Calculer les statistiques
        $summary = [
            'total_hours' => round($records->sum('total_minutes') / 60, 2),
            'total_overtime_hours' => round($records->sum('overtime_minutes') / 60, 2),
            'total_absences' => $records->where('is_absent', true)->count(),
            'total_working_days' => $records->where('is_absent', false)->whereNotNull('check_in_time')->count(),
            'total_records' => $records->count(),
        ];

        // Si un employé spécifique, calculer aussi les repos
        if ($employee) {
            $restDays = EmployeeRestDay::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            $summary['total_rest_days'] = $restDays;
        } else {
            $restDays = EmployeeRestDay::whereBetween('date', [$startDate, $endDate])
                ->distinct('employee_id', 'date')
                ->count();
            $summary['total_rest_days'] = $restDays;
        }

        $pdf = Pdf::loadView('reports.monthly-pdf', compact(
            'employee',
            'summary',
            'records',
            'startDate',
            'endDate',
            'year',
            'month'
        ));

        $filename = 'rapport-pointage-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        if ($employee) {
            $filename .= '-' . str_replace(' ', '-', strtolower($employee->full_name));
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report to Excel.
     */
    public function exportExcel(Request $request)
    {
        // This would require a library like maatwebsite/excel
        // For now, return JSON or redirect to monthly report
        return redirect()->route('reports.monthly', $request->all());
    }
}
