@extends('layouts.app')

@section('title', 'Comptabilisation des Heures Supplémentaires')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Comptabilisation Mensuelle des Heures Supplémentaires</h5>
        <a href="{{ route('overtime.report', ['year' => $year, 'month' => $month, 'employee_id' => $employeeId]) }}" class="btn btn-primary">
            <i class="bx bx-file"></i> Générer le Rapport
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('overtime.accounting') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Année</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mois</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(null, $m, 1)->locale('fr')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Employé (optionnel)</label>
                    <select name="employee_id" class="form-select">
                        <option value="">Tous les employés</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i> Filtrer
                    </button>
                </div>
            </div>
        </form>

        <!-- Summary Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-label-primary">
                    <div class="card-body text-center">
                        <h6 class="text-primary">Total des heures supplémentaires pour {{ Carbon\Carbon::create($year, $month, 1)->locale('fr')->monthName }} {{ $year }}</h6>
                        <h2 class="text-primary">{{ number_format($totalHours, 2) }} heures</h2>
                        @if($employeeId)
                            <p class="mb-0">Pour: {{ $employees->firstWhere('id', $employeeId)->full_name ?? '-' }}</p>
                        @else
                            <p class="mb-0">Tous les employés</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Code</th>
                        <th>Total Heures Supp</th>
                        <th>Heures Manuelles</th>
                        <th>Heures Automatiques</th>
                        <th>Nombre de jours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryByEmployee as $summary)
                    <tr>
                        <td><strong>{{ $summary['employee']->full_name }}</strong></td>
                        <td>{{ $summary['employee']->employee_code }}</td>
                        <td><strong class="text-primary">{{ number_format($summary['total_hours'], 2) }}h</strong></td>
                        <td>{{ number_format($summary['manual_hours'], 2) }}h</td>
                        <td>{{ number_format($summary['auto_hours'], 2) }}h</td>
                        <td>{{ $summary['count'] }} jour(s)</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucune heure supplémentaire enregistrée pour cette période</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($summaryByEmployee->count() > 0)
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="2">TOTAL</th>
                        <th>{{ number_format($summaryByEmployee->sum('total_hours'), 2) }}h</th>
                        <th>{{ number_format($summaryByEmployee->sum('manual_hours'), 2) }}h</th>
                        <th>{{ number_format($summaryByEmployee->sum('auto_hours'), 2) }}h</th>
                        <th>{{ $summaryByEmployee->sum('count') }} jour(s)</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

