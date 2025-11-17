@extends('layouts.employee')

@section('title', 'Historique des Pointages')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Historique de mes Pointages</h5>
            </div>
            <div class="card-body">
                <!-- Filtres -->
                <form method="GET" action="{{ route('employee.attendance-history') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date de début</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date de fin</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('employee.attendance-history') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Tableau des pointages -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Site</th>
                                <th>Entrée</th>
                                <th>Sortie</th>
                                <th>Heures travaillées</th>
                                <th>Heures supplémentaires</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                            <tr>
                                <td>
                                    <strong>{{ $record->date->format('d/m/Y') }}</strong><br>
                                    <small class="text-muted">{{ $record->date->locale('fr')->dayName }}</small>
                                </td>
                                <td>{{ $record->site ? $record->site->name : '-' }}</td>
                                <td>
                                    @if($record->check_in_time)
                                        <span class="badge bg-label-success">
                                            {{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->check_out_time)
                                        <span class="badge bg-label-info">
                                            {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->check_in_time && $record->check_out_time)
                                        <strong>{{ $record->total_hours }}h</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->overtime_hours > 0)
                                        <span class="badge bg-label-warning">{{ $record->overtime_hours }}h</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->is_absent)
                                        <span class="badge bg-label-danger">Absent</span>
                                    @elseif($record->is_late)
                                        <span class="badge bg-label-warning">
                                            Retard
                                            @if($record->late_minutes > 0)
                                                ({{ $record->late_minutes }} min)
                                            @endif
                                        </span>
                                    @elseif($record->check_in_time && $record->check_out_time)
                                        <span class="badge bg-label-success">Normal</span>
                                    @elseif($record->check_in_time)
                                        <span class="badge bg-label-info">Pointé entrée</span>
                                    @else
                                        <span class="badge bg-label-secondary">Non pointé</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">Aucun pointage trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($records->hasPages())
                <div class="mt-3">
                    {{ $records->links() }}
                </div>
                @endif

                <!-- Statistiques -->
                @if($records->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-label-primary">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">Statistiques de la période</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Total de jours:</strong></p>
                                        <h4>{{ $records->total() }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Jours pointés:</strong></p>
                                        <h4>{{ $records->where('check_in_time', '!=', null)->count() }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Jours absents:</strong></p>
                                        <h4>{{ $records->where('is_absent', true)->count() }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Retards:</strong></p>
                                        <h4>{{ $records->where('is_late', true)->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection





