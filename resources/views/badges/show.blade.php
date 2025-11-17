@extends('layouts.app')

@section('title', 'Détails Badge')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détails du Badge</h5>
        <div>
            <a href="{{ route('badges.edit', $badge) }}" class="btn btn-warning">
                <i class="bx bx-edit"></i> Modifier
            </a>
            <a href="{{ route('badges.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informations du Badge</h6>
                <p><strong>Numéro de Badge:</strong> {{ $badge->badge_number }}</p>
                <p><strong>Code QR:</strong> <code>{{ $badge->qr_code }}</code></p>
                <p><strong>Date d'émission:</strong> {{ $badge->issued_at ? $badge->issued_at->format('d/m/Y') : '-' }}</p>
                <p><strong>Date d'expiration:</strong> 
                    @if($badge->expires_at)
                        @if($badge->isExpired())
                            <span class="text-danger">{{ $badge->expires_at->format('d/m/Y') }} (Expiré)</span>
                        @else
                            {{ $badge->expires_at->format('d/m/Y') }}
                        @endif
                    @else
                        <span class="text-muted">Sans expiration</span>
                    @endif
                </p>
                <p><strong>Statut:</strong> 
                    @if($badge->is_active && !$badge->isExpired())
                        <span class="badge bg-label-success">Actif</span>
                    @elseif($badge->isExpired())
                        <span class="badge bg-label-danger">Expiré</span>
                    @else
                        <span class="badge bg-label-secondary">Inactif</span>
                    @endif
                </p>
                @if($badge->notes)
                <p><strong>Notes:</strong> {{ $badge->notes }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <h6>Informations de l'Employé</h6>
                <p><strong>Nom:</strong> {{ $badge->employee->full_name }}</p>
                <p><strong>Code Employé:</strong> {{ $badge->employee->employee_code }}</p>
                <p><strong>Email:</strong> {{ $badge->employee->email }}</p>
                <p><strong>Téléphone:</strong> {{ $badge->employee->phone ?? '-' }}</p>
                <p><strong>Département:</strong> {{ $badge->employee->department->name ?? '-' }}</p>
                <p><strong>Poste:</strong> {{ $badge->employee->position ?? '-' }}</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row mb-4">
            <div class="col-12">
                <h6>QR Code du Badge</h6>
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <p class="text-muted mb-3">Ce QR code permet à l'employé de pointer avec son badge physique</p>
                        <div class="mb-3 d-flex justify-content-center">
                            <div style="background: white; padding: 10px; border-radius: 8px; display: inline-block;">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="{{ route('badges.print', $badge) }}" class="btn btn-primary" target="_blank">
                                <i class="bx bx-printer"></i> Imprimer le Badge
                            </a>
                            <a href="{{ route('badges.download-qr', $badge) }}" class="btn btn-success">
                                <i class="bx bx-download"></i> Télécharger QR Code (PNG)
                            </a>
                        </div>
                        <p class="text-muted mt-2 mb-0"><small>Code: {{ $badge->qr_code }}</small></p>
                        <div class="alert alert-info mt-3">
                            <i class="bx bx-info-circle"></i> <strong>Instructions:</strong> Cliquez sur "Imprimer le Badge" pour voir le design complet du badge avec toutes les informations. Le badge est prêt à être imprimé et plastifié.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

