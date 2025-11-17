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
                    <div class="card-body text-center">
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="badge-preview-container">
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
                                <i class="bx bx-download"></i> Télécharger Badge (PNG)
                            </a>
                        </div>
                        <p class="text-muted mt-2 mb-0"><small>Code QR: {{ $badge->qr_code }}</small></p>
                        <div class="alert alert-info mt-3">
                            <i class="bx bx-info-circle"></i> <strong>Instructions:</strong> Le badge est prêt à être imprimé et plastifié. Cliquez sur "Télécharger Badge (PNG)" pour obtenir une image haute résolution.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @push('page-css')
        <style>
            .badge-preview-container {
                width: 85.6mm !important;
                height: 53.98mm !important;
                min-width: 324px !important;
                min-height: 204px !important;
                background: linear-gradient(135deg, #074136 0%, #0a5a4a 100%) !important;
                border-radius: 8px !important;
                padding: 12px !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                position: relative !important;
                overflow: hidden !important;
                margin: 0 auto !important;
                color: white !important;
                box-sizing: border-box !important;
            }
            
            .badge-preview-container::before {
                content: '' !important;
                position: absolute !important;
                top: -50% !important;
                right: -50% !important;
                width: 200% !important;
                height: 200% !important;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%) !important;
                pointer-events: none !important;
            }
            
            .badge-preview-container .badge-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 8px !important;
                position: relative !important;
                z-index: 1 !important;
            }
            
            .badge-preview-container .logo-section {
                display: flex !important;
                align-items: center !important;
            }
            
            .badge-preview-container .logo-text {
                font-family: 'Georgia', 'Times New Roman', serif !important;
                font-size: 14px !important;
                font-weight: bold !important;
                letter-spacing: 0.5px !important;
                line-height: 1.2 !important;
                color: white !important;
            }
            
            .badge-preview-container .logo-sub {
                font-family: 'Arial', sans-serif !important;
                font-size: 7px !important;
                font-weight: normal !important;
                letter-spacing: 1.5px !important;
                margin-top: 1px !important;
                opacity: 0.9 !important;
                color: white !important;
            }
            
            .badge-preview-container .badge-number {
                background: rgba(255, 255, 255, 0.2) !important;
                padding: 4px 8px !important;
                border-radius: 4px !important;
                font-size: 9px !important;
                font-weight: bold !important;
                letter-spacing: 0.5px !important;
                color: white !important;
            }
            
            .badge-preview-container .badge-body {
                display: flex !important;
                gap: 10px !important;
                position: relative !important;
                z-index: 1 !important;
                flex-direction: row !important;
            }
            
            .badge-preview-container .employee-info {
                flex: 1 !important;
                min-width: 0 !important;
            }
            
            .badge-preview-container .employee-name {
                font-size: 16px !important;
                font-weight: bold !important;
                margin-bottom: 4px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                color: white !important;
            }
            
            .badge-preview-container .employee-details {
                font-size: 9px !important;
                line-height: 1.4 !important;
                opacity: 0.95 !important;
                color: white !important;
            }
            
            .badge-preview-container .employee-detail-item {
                margin-bottom: 2px !important;
                color: white !important;
            }
            
            .badge-preview-container .employee-detail-label {
                font-weight: bold !important;
                display: inline-block !important;
                width: 50px !important;
                color: white !important;
            }
            
            .badge-preview-container .qr-section {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                flex-shrink: 0 !important;
                width: 60px !important;
            }
            
            .badge-preview-container .qr-code-wrapper {
                background: white !important;
                padding: 4px !important;
                border-radius: 4px !important;
                margin-bottom: 4px !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
                width: 55px !important;
                height: 55px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
            }
            
            .badge-preview-container .qr-code-wrapper svg {
                display: block !important;
                width: 47px !important;
                height: 47px !important;
                max-width: 47px !important;
                max-height: 47px !important;
            }
            
            .badge-preview-container .qr-label {
                font-size: 7px !important;
                text-align: center !important;
                opacity: 0.9 !important;
                margin-top: 2px !important;
                color: white !important;
            }
            
            .badge-preview-container .badge-footer {
                margin-top: 8px !important;
                padding-top: 6px !important;
                border-top: 1px solid rgba(255, 255, 255, 0.3) !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                font-size: 8px !important;
                position: relative !important;
                z-index: 1 !important;
            }
            
            .badge-preview-container .department {
                font-weight: bold !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                color: white !important;
            }
            
            .badge-preview-container .validity {
                opacity: 0.8 !important;
                color: white !important;
            }
        </style>
        @endpush
    </div>
</div>
@endsection

