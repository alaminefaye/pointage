@extends('layouts.app')

@section('title', 'Heures Supplémentaires')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Heures Supplémentaires</h5>
        <div>
            <a href="{{ route('overtime.accounting') }}" class="btn btn-info me-2">
                <i class="bx bx-calculator"></i> Comptabilisation
            </a>
            <a href="{{ route('overtime.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Ajouter des heures supplémentaires
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" action="{{ route('overtime.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Employé</label>
                    <select name="employee_id" class="form-select">
                        <option value="">Tous les employés</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manuel</option>
                        <option value="auto" {{ request('type') == 'auto' ? 'selected' : '' }}>Automatique</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-search"></i> Filtrer
                    </button>
                    <a href="{{ route('overtime.index') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employé</th>
                        <th>Heures supplémentaires</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimeRecords as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        <td>{{ $record->employee->full_name }}</td>
                        <td><strong>{{ number_format($record->hours, 2) }}h</strong></td>
                        <td>
                            @if($record->type === 'manual')
                                <span class="badge bg-label-primary">Manuel</span>
                            @else
                                <span class="badge bg-label-info">Automatique</span>
                            @endif
                        </td>
                        <td>{{ $record->notes ?? '-' }}</td>
                        <td>
                            @if($record->type === 'manual')
                                <a href="{{ route('overtime.edit', $record) }}" class="btn btn-sm btn-warning">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <form action="{{ route('overtime.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucune heure supplémentaire enregistrée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $overtimeRecords->links() }}
        </div>
    </div>
</div>
@endsection

