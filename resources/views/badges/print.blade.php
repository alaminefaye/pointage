<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge - {{ $badge->employee->full_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .badge-container {
            width: 85.6mm; /* Taille standard d'un badge */
            height: 53.98mm; /* Taille standard d'un badge */
            background: linear-gradient(135deg, #074136 0%, #0a5a4a 100%);
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            margin: 0 auto;
            color: white;
        }
        
        .badge-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .badge-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
        }
        
        .logo-text {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        
        .logo-sub {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            font-weight: normal;
            letter-spacing: 1.5px;
            margin-top: 1px;
            opacity: 0.9;
        }
        
        .badge-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .badge-body {
            display: flex;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        
        .employee-info {
            flex: 1;
        }
        
        .employee-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .employee-details {
            font-size: 9px;
            line-height: 1.4;
            opacity: 0.95;
        }
        
        .employee-detail-item {
            margin-bottom: 2px;
        }
        
        .employee-detail-label {
            font-weight: bold;
            display: inline-block;
            width: 50px;
        }
        
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .qr-code-wrapper {
            background: white;
            padding: 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .qr-code-wrapper svg {
            display: block;
        }
        
        .qr-label {
            font-size: 7px;
            text-align: center;
            opacity: 0.9;
            margin-top: 2px;
        }
        
        .badge-footer {
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
        
        .department {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .validity {
            opacity: 0.8;
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .badge-container {
                margin: 0;
                page-break-inside: avoid;
                box-shadow: none;
            }
            
            .no-print {
                display: none;
            }
        }
        
        /* Page layout for multiple badges */
        .print-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }
        
        @media print {
            .print-page {
                grid-template-columns: repeat(2, 1fr);
                gap: 10mm;
                padding: 10mm;
            }
        }
        
        .print-controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .print-controls button {
            background: #074136;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 0 10px;
            transition: background 0.3s;
        }
        
        .print-controls button:hover {
            background: #052d25;
        }
        
        .print-controls a {
            display: inline-block;
            text-decoration: none;
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            margin: 0 10px;
            transition: background 0.3s;
        }
        
        .print-controls a:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <h2 style="color: #074136; margin-bottom: 15px;">Badge d'Identification</h2>
        <p style="color: #666; margin-bottom: 20px;">{{ $badge->employee->full_name }} - {{ $badge->badge_number }}</p>
        <button onclick="window.print()">
            <i class="bx bx-printer"></i> Imprimer le Badge
        </button>
        <a href="{{ route('badges.show', $badge) }}">
            <i class="bx bx-arrow-back"></i> Retour
        </a>
    </div>
    
    <div class="print-page">
        <!-- Badge 1 -->
        <div class="badge-container">
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
        
        <!-- Badge 2 (duplicate for printing) -->
        <div class="badge-container">
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
</body>
</html>

