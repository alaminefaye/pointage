@extends('layouts.app')

@section('title', 'Pointages du Jour')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pointage /</span> Pointages du Jour
    </h4>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Employés</span>
                    <h3 class="card-title mb-2">{{ $totalEmployees }}</h3>
                    <small class="text-muted">Employés actifs</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-log-in text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Pointés Entrée</span>
                    <h3 class="card-title mb-2">{{ $checkedIn }}</h3>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-log-out text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Pointés Sortie</span>
                    <h3 class="card-title mb-2">{{ $checkedOut }}</h3>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-bed text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Au Repos</span>
                    <h3 class="card-title mb-2">{{ $onRest }}</h3>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Records -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pointages du {{ $today->format('d/m/Y') }}</h5>
        </div>
        <div class="card-body">
            @if($todayRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Site</th>
                                <th>Heure Entrée</th>
                                <th>Heure Sortie</th>
                                <th>Durée</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayRecords as $record)
                                <tr>
                                    <td>
                                        <strong>{{ $record->employee->full_name }}</strong>
                                    </td>
                                    <td>{{ $record->employee->department->name ?? '-' }}</td>
                                    <td>{{ $record->site->name ?? '-' }}</td>
                                    <td>
                                        @if($record->check_in_time)
                                            <span class="badge bg-label-success">
                                                {{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->check_out_time)
                                            <span class="badge bg-label-warning">
                                                {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">En cours</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->check_in_time && $record->check_out_time)
                                            {{ number_format($record->total_minutes / 60, 1) }}h
                                        @elseif($record->check_in_time)
                                            <span class="text-muted">En cours...</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->is_absent)
                                            <span class="badge bg-label-danger">Absent</span>
                                        @elseif($record->check_in_time && !$record->check_out_time)
                                            <span class="badge bg-label-info">En cours</span>
                                        @elseif($record->check_in_time && $record->check_out_time)
                                            <span class="badge bg-label-success">Complet</span>
                                        @else
                                            <span class="badge bg-label-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i> Aucun pointage enregistré pour aujourd'hui.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

