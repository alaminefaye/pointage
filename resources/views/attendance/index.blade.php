@extends('layouts.app')

@section('title', 'Pointage')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historique des Pointages</h5>
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

        <div class="alert alert-info mb-3">
            <i class="bx bx-info-circle"></i> <strong>Astuce :</strong> Pour marquer un jour comme jour de repos, cliquez sur l'icône <i class="bx bx-calendar-check"></i> à côté d'un statut "Absent" dans le tableau ci-dessous, ou allez dans <strong>Employés</strong> → Cliquez sur l'icône <i class="bx bx-show"></i> d'un employé → Section "Jours de repos".
        </div>

        <form method="GET" action="{{ route('attendance.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label">Employé</label>
                    <select class="form-select" name="employee_id">
                        <option value="">Tous</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Filtrer</button>
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Site</th>
                        <th>Employé</th>
                        <th>Entrée</th>
                        <th>Sortie</th>
                        <th>Heures</th>
                        <th>Heures Sup</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        <td>{{ $record->site ? $record->site->name : '-' }}</td>
                        <td>{{ $record->employee->full_name }}</td>
                        <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->total_hours }}h</td>
                        <td>{{ $record->overtime_hours }}h</td>
                        <td>
                            @if($record->is_absent)
                                <span class="badge bg-label-danger">Absent</span>
                            @elseif($record->is_late)
                                <span class="badge bg-label-warning">En retard</span>
                            @else
                                <span class="badge bg-label-success">Normal</span>
                            @endif
                        </td>
                        <td>
                            @if($record->is_absent)
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#markRestDayModal{{ $record->id }}" title="Marquer comme jour de repos">
                                    <i class="bx bx-calendar-check"></i>
                                </button>
                                
                                <!-- Modal pour marquer comme jour de repos -->
                                <div class="modal fade" id="markRestDayModal{{ $record->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Marquer comme jour de repos</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rest-days.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $record->employee_id }}">
                                                <input type="hidden" name="date" value="{{ $record->date->format('Y-m-d') }}">
                                                <div class="modal-body">
                                                    <p>Marquer le <strong>{{ $record->date->format('d/m/Y') }}</strong> comme jour de repos pour <strong>{{ $record->employee->full_name }}</strong> ?</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Raison (optionnel)</label>
                                                        <textarea class="form-control" name="reason" rows="3" placeholder="Ex: Congé, Férié, etc."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Marquer comme repos</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Aucun pointage trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection

