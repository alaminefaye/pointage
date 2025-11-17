@extends('layouts.app')

@section('title', 'Rapport Mensuel des Pointages')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Rapport Mensuel des Pointages
            @if($employee)
                - {{ $employee->full_name }}
            @else
                - Tous les employés
            @endif
        </h5>
        <div>
            <a href="{{ route('reports.export-pdf', ['year' => $year, 'month' => $month, 'employee_id' => $employee?->id]) }}" class="btn btn-danger me-2" target="_blank">
                <i class="bx bx-file"></i> Télécharger en PDF
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Retour
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
                        <p class="mb-0"><strong>Employé:</strong> {{ $employee->full_name }} ({{ $employee->employee_code }}) - {{ $employee->department->name ?? 'N/A' }}</p>
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
                        <h6 class="text-primary">Heures Totales</h6>
                        <h3 class="text-primary">{{ number_format($summary['total_hours'], 2) }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-success">
                    <div class="card-body text-center">
                        <h6 class="text-success">Heures Supplémentaires</h6>
                        <h3 class="text-success">{{ number_format($summary['total_overtime_hours'], 2) }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-danger">
                    <div class="card-body text-center">
                        <h6 class="text-danger">Absences</h6>
                        <h3 class="text-danger">{{ $summary['total_absences'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-label-info">
                    <div class="card-body text-center">
                        <h6 class="text-info">Jours de Repos</h6>
                        <h3 class="text-info">{{ $summary['total_rest_days'] ?? 0 }}</h3>
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
                        @if(!$employee)
                        <th>Employé</th>
                        <th>Code</th>
                        <th>Département</th>
                        @endif
                        <th>Entrée</th>
                        <th>Sortie</th>
                        <th>Heures</th>
                        <th>Heures Sup</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        @if(!$employee)
                        <td>{{ $record->employee->full_name }}</td>
                        <td>{{ $record->employee->employee_code }}</td>
                        <td>{{ $record->employee->department->name ?? '-' }}</td>
                        @endif
                        <td>
                            @if($record->check_in_time)
                                <span class="badge bg-label-success">{{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($record->check_out_time)
                                <span class="badge bg-label-warning">{{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                        </td>
                        <td>{{ number_format($record->total_minutes / 60, 2) }}h</td>
                        <td>
                            @php
                                $employeeId = $record->employee_id;
                                $monthlyOvertime = $monthlyOvertimeByEmployee[$employeeId] ?? 0;
                            @endphp
                            <strong>{{ number_format($monthlyOvertime, 2) }}h</strong>
                        </td>
                        <td>
                            @if($record->is_absent)
                                <span class="badge bg-label-danger">Absent</span>
                            @elseif($record->check_in_time && $record->check_out_time)
                                <span class="badge bg-label-success">Complet</span>
                            @elseif($record->check_in_time)
                                <span class="badge bg-label-info">En cours</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $employee ? 9 : 12 }}" class="text-center">Aucun pointage enregistré pour cette période</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

