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
                            <div class="badge-preview-container" style="width: 85.6mm; height: 53.98mm; background: linear-gradient(135deg, #074136 0%, #0a5a4a 100%); border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); position: relative; overflow: hidden; color: white;">
                                <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%); pointer-events: none;"></div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; position: relative; z-index: 1;">
                                    <div>
                                        <div style="font-family: 'Georgia', 'Times New Roman', serif; font-size: 14px; font-weight: bold; letter-spacing: 0.5px; line-height: 1.2;">GASPARD</div>
                                        <div style="font-family: 'Arial', sans-serif; font-size: 7px; font-weight: normal; letter-spacing: 1.5px; margin-top: 1px; opacity: 0.9;">SIGNATURE</div>
                                    </div>
                                    <div style="background: rgba(255, 255, 255, 0.2); padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; letter-spacing: 0.5px;">#{{ $badge->badge_number }}</div>
                                </div>
                                
                                <div style="display: flex; gap: 10px; position: relative; z-index: 1;">
                                    <div style="flex: 1;">
                                        <div style="font-size: 16px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $badge->employee->full_name }}</div>
                                        <div style="font-size: 9px; line-height: 1.4; opacity: 0.95;">
                                            <div style="margin-bottom: 2px;"><span style="font-weight: bold; display: inline-block; width: 50px;">Code:</span><span>{{ $badge->employee->employee_code }}</span></div>
                                            @if($badge->employee->position)
                                            <div style="margin-bottom: 2px;"><span style="font-weight: bold; display: inline-block; width: 50px;">Poste:</span><span>{{ $badge->employee->position }}</span></div>
                                            @endif
                                            @if($badge->employee->department)
                                            <div style="margin-bottom: 2px;"><span style="font-weight: bold; display: inline-block; width: 50px;">Dépt:</span><span>{{ $badge->employee->department->name }}</span></div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; width: 60px;">
                                        <div style="background: white; padding: 4px; border-radius: 4px; margin-bottom: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                            <div style="width: 100%; height: 100%; max-width: 47px; max-height: 47px;">
                                                {!! $qrCodeSvg !!}
                                            </div>
                                        </div>
                                        <div style="font-size: 7px; text-align: center; opacity: 0.9; margin-top: 2px;">SCAN ME</div>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid rgba(255, 255, 255, 0.3); display: flex; justify-content: space-between; align-items: center; font-size: 8px; position: relative; z-index: 1;">
                                    <div style="font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">{{ $badge->employee->department->name ?? 'N/A' }}</div>
                                    <div style="opacity: 0.8;">
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
    </div>
</div>
@endsection

