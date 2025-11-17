@extends('layouts.app')

@section('title', 'Détails Employé')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détails de l'Employé</h5>
        <div>
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                <i class="bx bx-edit"></i> Modifier
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Retour</a>
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

        <div class="row">
            <div class="col-md-6">
                <h6>Informations personnelles</h6>
                <p><strong>Code:</strong> {{ $employee->employee_code }}</p>
                <p><strong>Nom complet:</strong> {{ $employee->full_name }}</p>
                <p><strong>Email:</strong> {{ $employee->email }}</p>
                <p><strong>Téléphone:</strong> {{ $employee->phone ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <h6>Informations professionnelles</h6>
                <p><strong>Département:</strong> {{ $employee->department->name }}</p>
                <p><strong>Poste:</strong> {{ $employee->position }}</p>
                <p><strong>Heures/jour:</strong> {{ $employee->standard_hours_per_day }}h</p>
                <p><strong>Seuil d'heures supplémentaires:</strong> 
                    @if($employee->overtime_threshold_hours)
                        {{ number_format($employee->overtime_threshold_hours, 1) }}h/jour
                    @else
                        <span class="text-muted">Global ({{ \App\Models\AttendanceSetting::getValue(null, 'overtime_threshold_hours', 10) }}h)</span>
                    @endif
                </p>
                <p><strong>Statut:</strong> 
                    @if($employee->is_active)
                        <span class="badge bg-label-success">Actif</span>
                    @else
                        <span class="badge bg-label-danger">Inactif</span>
                    @endif
                </p>
            </div>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Jours de repos</h6>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRestDayModal">
                <i class="bx bx-plus"></i> Ajouter un jour de repos
            </button>
        </div>
        
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Raison</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->restDays()->orderBy('date', 'desc')->take(10)->get() as $restDay)
                    <tr>
                        <td>{{ $restDay->date->format('d/m/Y') }}</td>
                        <td>{{ $restDay->reason ?? '-' }}</td>
                        <td>
                            <form action="{{ route('rest-days.destroy', $restDay) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce jour de repos?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Aucun jour de repos enregistré</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Modal pour ajouter un jour de repos -->
        <div class="modal fade" id="addRestDayModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un jour de repos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('rest-days.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Raison (optionnel)</label>
                                <textarea class="form-control" name="reason" rows="3" placeholder="Ex: Congé, Férié, etc."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <hr>
        
        <h6>Derniers pointages</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entrée</th>
                        <th>Sortie</th>
                        <th>Heures</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->attendanceRecords()->latest()->take(10)->get() as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->total_hours }}h</td>
                        <td>
                            @if($record->is_absent)
                                <span class="badge bg-label-danger">Absent</span>
                            @elseif($record->is_late)
                                <span class="badge bg-label-warning">Retard</span>
                            @else
                                <span class="badge bg-label-success">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Aucun pointage</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

