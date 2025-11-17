@extends('layouts.employee')

@section('title', 'Dashboard Employé')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Bienvenue, {{ $employee->full_name }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Code:</strong> {{ $employee->employee_code }}</p>
                        <p><strong>Département:</strong> {{ $employee->department->name }}</p>
                        <p><strong>Poste:</strong> {{ $employee->position }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Heures/jour:</strong> {{ $employee->standard_hours_per_day }}h</p>
                        <p class="text-muted"><small>Les heures supplémentaires sont calculées automatiquement si vous travaillez plus que ce nombre d'heures par jour.</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pointage du jour</h5>
            </div>
            <div class="card-body">
                @if($todayAttendance)
                    <p><strong>Entrée:</strong> {{ $todayAttendance->check_in_time ? \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') : 'Non pointé' }}</p>
                    <p><strong>Sortie:</strong> {{ $todayAttendance->check_out_time ? \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') : 'Non pointé' }}</p>
                    @if($todayAttendance->check_in_time && $todayAttendance->check_out_time)
                        <p><strong>Heures travaillées:</strong> {{ $todayAttendance->total_hours }}h</p>
                    @endif
                @else
                    <p class="text-muted">Aucun pointage aujourd'hui</p>
                @endif
                
                <div class="mt-3">
                    <a href="{{ route('employee.qr-scanner') }}" class="btn btn-primary">
                        <i class="bx bx-qr-scan"></i> Scanner QR Code
                    </a>
                    <a href="{{ route('employee.attendance-history') }}" class="btn btn-info">
                        <i class="bx bx-history"></i> Voir l'historique
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

