@extends('layouts.app')

@section('title', 'Rapport des Heures Supplémentaires')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Rapport des Heures Supplémentaires
            @if($employee)
                - {{ $employee->full_name }}
            @else
                - Tous les employés
            @endif
        </h5>
        <div>
            <a href="{{ route('overtime.export-pdf', ['year' => $year, 'month' => $month, 'employee_id' => $employee?->id]) }}" class="btn btn-danger me-2" target="_blank">
                <i class="bx bx-file"></i> Télécharger en PDF
            </a>
            <a href="{{ route('overtime.accounting', ['year' => $year, 'month' => $month, 'employee_id' => $employee?->id]) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Retour à la comptabilisation
            </a>
        </div>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Report Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h6 class="mb-2">Période: {{ $startDate->locale('fr')->isoFormat('DD MMMM YYYY') }} - {{ $endDate->locale('fr')->isoFormat('DD MMMM YYYY') }}</h6>
                    @if($employee)
                        <p class="mb-0"><strong>Employé:</strong> {{ $employee->full_name }} ({{ $employee->employee_code }})</p>
                    @else
                        <p class="mb-0"><strong>Employés:</strong> Tous</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-label-primary">
                    <div class="card-body text-center">
                        <h6 class="text-primary">Total Heures</h6>
                        <h3 class="text-primary">{{ number_format($summary['total_hours'], 2) }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-info">
                    <div class="card-body text-center">
                        <h6 class="text-info">Heures Manuelles</h6>
                        <h3 class="text-info">{{ number_format($summary['manual_hours'], 2) }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-success">
                    <div class="card-body text-center">
                        <h6 class="text-success">Heures Automatiques</h6>
                        <h3 class="text-success">{{ number_format($summary['auto_hours'], 2) }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-warning">
                    <div class="card-body text-center">
                        <h6 class="text-warning">Nombre d'enregistrements</h6>
                        <h3 class="text-warning">{{ $summary['total_records'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <h6 class="mb-3">Détails par jour</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employé</th>
                        <th>Code</th>
                        <th>Heures Supplémentaires</th>
                        <th>Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        <td>{{ $record->employee->full_name }}</td>
                        <td>{{ $record->employee->employee_code }}</td>
                        <td><strong>{{ number_format($record->hours, 2) }}h</strong></td>
                        <td>
                            @if($record->type === 'manual')
                                <span class="badge bg-label-primary">Manuel</span>
                            @else
                                <span class="badge bg-label-info">Automatique</span>
                            @endif
                        </td>
                        <td>{{ $record->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucune heure supplémentaire enregistrée pour cette période</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($records->count() > 0)
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="3">TOTAL</th>
                        <th>{{ number_format($records->sum('hours'), 2) }}h</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Generate Report Form -->
        <hr>
        <div class="row mt-4">
            <div class="col-md-12">
                <h6>Générer un autre rapport</h6>
                <form method="GET" action="{{ route('overtime.report') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Année</label>
                        <select name="year" class="form-select" required>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (int)$year == (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mois</label>
                        <select name="month" class="form-select" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)$month == (int)$m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create(null, $m, 1)->locale('fr')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employé (optionnel - laisser vide pour tous)</label>
                        <select name="employee_id" class="form-select">
                            <option value="">Tous les employés</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ ($employee && $employee->id == $emp->id) ? 'selected' : '' }}>
                                    {{ $emp->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i> Générer
                        </button>
                    </div>
                </form>
                <div class="mt-2">
                    <small class="text-muted">Les paramètres seront appliqués lors de la soumission du formulaire.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

