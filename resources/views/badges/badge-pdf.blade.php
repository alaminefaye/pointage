<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Badge - {{ $badge->employee->full_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        
        .badge-container {
            width: 85.6mm;
            height: 53.98mm;
            background-color: #074136;
            padding: 12px;
            color: white;
            margin: 120mm auto 0 auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-text {
            font-family: Georgia, serif;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .logo-sub {
            font-family: Arial, sans-serif;
            font-size: 7px;
            letter-spacing: 1.5px;
            margin-top: 1px;
        }
        
        .badge-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: right;
        }
        
        .employee-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        
        .employee-details {
            font-size: 9px;
            line-height: 1.4;
        }
        
        .employee-detail-label {
            font-weight: bold;
        }
        
        .qr-code-wrapper {
            background: white;
            padding: 4px;
            width: 55px;
            height: 55px;
            text-align: center;
        }
        
        .qr-code-wrapper img {
            width: 47px;
            height: 47px;
        }
        
        .qr-label {
            font-size: 7px;
            text-align: center;
            margin-top: 2px;
        }
        
        .badge-footer {
            padding-top: 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 8px;
        }
        
        .department {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .validity {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="badge-container">
        <!-- Header -->
        <table>
            <tr>
                <td>
                    <div class="logo-text">GASPARD</div>
                    <div class="logo-sub">SIGNATURE</div>
                </td>
                <td class="badge-number">#{{ $badge->badge_number }}</td>
            </tr>
        </table>
        
        <!-- Body -->
        <table style="margin-top: 8px;">
            <tr>
                <td style="vertical-align: top;">
                    <div class="employee-name">{{ $badge->employee->full_name }}</div>
                    <div class="employee-details">
                        <div><span class="employee-detail-label">Code:</span> {{ $badge->employee->employee_code }}</div>
                        @if($badge->employee->position)
                        <div><span class="employee-detail-label">Poste:</span> {{ $badge->employee->position }}</div>
                        @endif
                        @if($badge->employee->department)
                        <div><span class="employee-detail-label">Dépt:</span> {{ $badge->employee->department->name }}</div>
                        @endif
                    </div>
                </td>
                <td style="width: 60px; text-align: center; vertical-align: middle;">
                    <div class="qr-code-wrapper">
                        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" />
                    </div>
                    <div class="qr-label">SCAN ME</div>
                </td>
            </tr>
        </table>
        
        <!-- Footer -->
        <div class="badge-footer">
            <table>
                <tr>
                    <td class="department">{{ $badge->employee->department->name ?? 'N/A' }}</td>
                    <td class="validity">
                        @if($badge->expires_at)
                            Valide jusqu'au {{ $badge->expires_at->format('m/Y') }}
                        @else
                            Valide indéfiniment
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
