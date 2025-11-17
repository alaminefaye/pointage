@extends('layouts.app')

@section('title', 'Rapport Mensuel')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Rapport Mensuel - {{ $employee->full_name }}</h5>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Heures totales</h6>
                        <h3>{{ $summary['total_hours'] }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Heures supplémentaires</h6>
                        <h3>{{ $summary['total_overtime_hours'] }}h</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Absences</h6>
                        <h3>{{ $summary['absences'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Retards</h6>
                        <h3>{{ $summary['lates'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <h6>Détails quotidiens</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
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
                        <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->total_hours }}h</td>
                        <td>{{ $record->overtime_hours }}h</td>
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
                        <td colspan="6" class="text-center">Aucun pointage</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

