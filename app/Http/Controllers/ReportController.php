<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ], [
            'employee_id.required' => 'Veuillez sélectionner un employé.',
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

        $employee = Employee::findOrFail($validated['employee_id']);
        $summary = $this->calculationService->calculateMonthlySummary(
            $employee->id,
            $validated['year'],
            $validated['month']
        );

        $startDate = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return view('reports.monthly', compact('employee', 'summary', 'records', 'startDate', 'endDate'));
    }

    /**
     * Export report to PDF.
     */
    public function exportPdf(Request $request)
    {
        // This would require a PDF library like dompdf or barryvdh/laravel-dompdf
        // For now, return JSON or redirect to monthly report
        return redirect()->route('reports.monthly', $request->all());
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
