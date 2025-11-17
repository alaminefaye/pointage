@extends('layouts.app')

@section('title', 'Pointages du Jour')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pointage /</span> Pointages du Jour
    </h4>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
        
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
        
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
        
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
            <!-- Search Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bx bx-search"></i> Recherche et Filtres</h6>
                    <form method="GET" action="{{ route('attendance.today') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom, email...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Employé</label>
                            <select class="form-select" name="employee_id">
                                <option value="">Tous</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Département</label>
                            <select class="form-select" name="department_id">
                                <option value="">Tous</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Site</label>
                            <select class="form-select" name="site_id">
                                <option value="">Tous</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous</option>
                                <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>En cours</option>
                                <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Complet</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="rest" {{ request('status') == 'rest' ? 'selected' : '' }}>Au Repos</option>
                                <option value="none" {{ request('status') == 'none' ? 'selected' : '' }}>Sans statut</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'employee_id', 'department_id', 'site_id', 'status']))
                        <div class="col-md-12">
                            <a href="{{ route('attendance.today') }}" class="btn btn-sm btn-secondary">
                                <i class="bx bx-x"></i> Réinitialiser
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
            @if($employeeStatusesPaginated->count() > 0)
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
                            @foreach($employeeStatusesPaginated as $item)
                                @php
                                    $employee = $item['employee'];
                                    $status = $item['status'];
                                    $attendance = $item['attendance'];
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $employee->full_name }}</strong>
                                    </td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>
                                    <td>{{ $attendance && $attendance->site ? $attendance->site->name : '-' }}</td>
                                    <td>
                                        @if($attendance && $attendance->check_in_time)
                                            <span class="badge bg-label-success">
                                                {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance && $attendance->check_out_time)
                                            <span class="badge bg-label-warning">
                                                {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') }}
                                            </span>
                                        @elseif($attendance && $attendance->check_in_time)
                                            <span class="badge bg-label-info">En cours</span>
                                        @else
                                            <span class="badge bg-label-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance && $attendance->check_in_time && $attendance->check_out_time)
                                            {{ number_format($attendance->total_minutes / 60, 1) }}h
                                        @elseif($attendance && $attendance->check_in_time)
                                            <span class="text-muted">En cours...</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($status === 'rest')
                                            <span class="badge bg-label-info">
                                                <i class="bx bx-bed"></i> Au Repos
                                            </span>
                                        @elseif($status === 'absent')
                                            <span class="badge bg-label-danger">
                                                <i class="bx bx-x-circle"></i> Absent
                                            </span>
                                        @elseif($status === 'checked_in')
                                            <span class="badge bg-label-info">
                                                <i class="bx bx-log-in"></i> En cours
                                            </span>
                                        @elseif($status === 'checked_out')
                                            <span class="badge bg-label-success">
                                                <i class="bx bx-check-circle"></i> Complet
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">
                                                <i class="bx bx-minus-circle"></i> Sans statut
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $employeeStatusesPaginated->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i> Aucun employé trouvé pour aujourd'hui.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

