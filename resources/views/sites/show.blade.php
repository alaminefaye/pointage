@extends('layouts.app')

@section('title', 'Détails Site')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détails du Site</h5>
        <div>
            <a href="{{ route('sites.edit', $site) }}" class="btn btn-warning">
                <i class="bx bx-edit"></i> Modifier
            </a>
            <a href="{{ route('sites.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informations</h6>
                <p><strong>Nom:</strong> {{ $site->name }}</p>
                <p><strong>Adresse:</strong> {{ $site->address ?? '-' }}</p>
                <p><strong>Description:</strong> {{ $site->description ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <h6>Géolocalisation</h6>
                <p><strong>Latitude:</strong> {{ $site->latitude }}</p>
                <p><strong>Longitude:</strong> {{ $site->longitude }}</p>
                <p><strong>Rayon autorisé:</strong> {{ $site->radius }}m</p>
                <p><strong>Statut:</strong> 
                    @if($site->is_active)
                        <span class="badge bg-label-success">Actif</span>
                    @else
                        <span class="badge bg-label-danger">Inactif</span>
                    @endif
                </p>
            </div>
        </div>
        
        <hr>
        
        @if($site->static_qr_code)
        <div class="row mb-4">
            <div class="col-12">
                <h6>QR Code pour Pointage</h6>
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <p class="text-muted mb-3">Scannez ce QR code avec l'application mobile pour pointer sur ce site</p>
                        <div class="mb-3 d-flex justify-content-center">
                            <div style="background: white; padding: 10px; border-radius: 8px; display: inline-block;">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                        <a href="{{ route('sites.download-qr', $site) }}" class="btn btn-primary">
                            <i class="bx bx-download"></i> Télécharger le QR Code
                        </a>
                        <p class="text-muted mt-2 mb-0"><small>Code: {{ $site->static_qr_code }}</small></p>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        @endif
        
        <h6>Pointages récents</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employé</th>
                        <th>Entrée</th>
                        <th>Sortie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($site->attendanceRecords()->with('employee')->latest()->take(10)->get() as $record)
                    <tr>
                        <td>{{ $record->date->format('d/m/Y') }}</td>
                        <td>{{ $record->employee->full_name }}</td>
                        <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                        <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Aucun pointage</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

