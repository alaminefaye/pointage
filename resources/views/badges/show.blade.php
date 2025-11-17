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
                <h6>Badge d'Identification</h6>
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="badge-preview-container" style="isolation: isolate;">
                                <div class="badge-header">
                                    <div class="logo-section">
                                        <div>
                                            <div class="logo-text">GASPARD</div>
                                            <div class="logo-sub">SIGNATURE</div>
                                        </div>
                                    </div>
                                    <div class="badge-number">#{{ $badge->badge_number }}</div>
                                </div>
                                
                                <div class="badge-body">
                                    <div class="employee-info">
                                        <div class="employee-name">{{ $badge->employee->full_name }}</div>
                                        <div class="employee-details">
                                            <div class="employee-detail-item">
                                                <span class="employee-detail-label">Code:</span>
                                                <span>{{ $badge->employee->employee_code }}</span>
                                            </div>
                                            @if($badge->employee->position)
                                            <div class="employee-detail-item">
                                                <span class="employee-detail-label">Poste:</span>
                                                <span>{{ $badge->employee->position }}</span>
                                            </div>
                                            @endif
                                            @if($badge->employee->department)
                                            <div class="employee-detail-item">
                                                <span class="employee-detail-label">Dépt:</span>
                                                <span>{{ $badge->employee->department->name }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="qr-section">
                                        <div class="qr-code-wrapper">
                                            {!! $qrCodeSvg !!}
                                        </div>
                                        <div class="qr-label">SCAN ME</div>
                                    </div>
                                </div>
                                
                                <div class="badge-footer">
                                    <div class="department">{{ $badge->employee->department->name ?? 'N/A' }}</div>
                                    <div class="validity">
                                        @if($badge->expires_at)
                                            Valide jusqu'au {{ $badge->expires_at->format('m/Y') }}
                                        @else
                                            Valide indéfiniment
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="{{ route('badges.print', $badge) }}" class="btn btn-primary" target="_blank">
                                <i class="bx bx-printer"></i> Imprimer le Badge
                            </a>
                            <a href="{{ route('badges.download-qr', $badge) }}" class="btn btn-success">
                                <i class="bx bx-download"></i> Télécharger Badge (PDF)
                            </a>
                        </div>
                        <p class="text-muted mt-2 mb-0"><small>Code QR: {{ $badge->qr_code }}</small></p>
                        <div class="alert alert-info mt-3">
                            <i class="bx bx-info-circle"></i> <strong>Instructions:</strong> Le badge est prêt à être imprimé et plastifié. Cliquez sur "Télécharger Badge (PDF)" pour obtenir un fichier PDF avec le même design que la page d'impression.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @push('page-css')
        <style>
            /* Copie exacte du CSS de print.blade.php */
            .badge-preview-container {
                width: 85.6mm;
                height: 53.98mm;
                background: linear-gradient(135deg, #074136 0%, #0a5a4a 100%);
                border-radius: 8px;
                padding: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                position: relative;
                overflow: hidden;
                margin: 0 auto;
                color: white;
            }
            
            .badge-preview-container::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
                pointer-events: none;
            }
            
            .badge-preview-container .badge-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
                position: relative;
                z-index: 1;
            }
            
            .badge-preview-container .logo-section {
                display: flex;
                align-items: center;
            }
            
            .badge-preview-container .logo-text {
                font-family: 'Georgia', 'Times New Roman', serif;
                font-size: 14px;
                font-weight: bold;
                letter-spacing: 0.5px;
                line-height: 1.2;
            }
            
            .badge-preview-container .logo-sub {
                font-family: 'Arial', sans-serif;
                font-size: 7px;
                font-weight: normal;
                letter-spacing: 1.5px;
                margin-top: 1px;
                opacity: 0.9;
            }
            
            .badge-preview-container .badge-number {
                background: rgba(255, 255, 255, 0.2);
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 9px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            .badge-preview-container .badge-body {
                display: flex;
                gap: 10px;
                position: relative;
                z-index: 1;
            }
            
            .badge-preview-container .employee-info {
                flex: 1;
            }
            
            .badge-preview-container .employee-name {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 4px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .badge-preview-container .employee-details {
                font-size: 9px;
                line-height: 1.4;
                opacity: 0.95;
            }
            
            .badge-preview-container .employee-detail-item {
                margin-bottom: 2px;
            }
            
            .badge-preview-container .employee-detail-label {
                font-weight: bold;
                display: inline-block;
                width: 50px;
            }
            
            .badge-preview-container .qr-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                width: 60px;
            }
            
            .badge-preview-container .qr-code-wrapper {
                background: white;
                padding: 4px;
                border-radius: 4px;
                margin-bottom: 4px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                width: 55px;
                height: 55px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            
            .badge-preview-container .qr-code-wrapper svg {
                display: block;
                width: 47px !important;
                height: 47px !important;
                max-width: 47px !important;
                max-height: 47px !important;
            }
            
            .badge-preview-container .qr-label {
                font-size: 7px;
                text-align: center;
                opacity: 0.9;
                margin-top: 2px;
            }
            
            .badge-preview-container .badge-footer {
                margin-top: 8px;
                padding-top: 6px;
                border-top: 1px solid rgba(255, 255, 255, 0.3);
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 8px;
                position: relative;
                z-index: 1;
            }
            
            .badge-preview-container .department {
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .badge-preview-container .validity {
                opacity: 0.8;
            }
        </style>
        @endpush
    </div>
</div>
@endsection

