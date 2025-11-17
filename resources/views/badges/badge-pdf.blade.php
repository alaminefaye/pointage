<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Badge - {{ $badge->employee->full_name }}</title>
    <style>
        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .badge-container {
            width: 85.6mm;
            height: 53.98mm;
            background-color: #074136;
            border-radius: 8px;
            padding: 12px;
            color: white;
        }
        
        .badge-header {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-text {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        
        .logo-sub {
            font-family: Arial, sans-serif;
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
            text-align: right;
        }
        
        .badge-body {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .body-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .employee-info {
            vertical-align: top;
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
            width: 60px;
            text-align: center;
            vertical-align: middle;
        }
        
        .qr-code-wrapper {
            background: white;
            padding: 4px;
            border-radius: 4px;
            margin-bottom: 4px;
            width: 55px;
            height: 55px;
            margin: 0 auto 4px auto;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .qr-code-wrapper img {
            width: 47px;
            height: 47px;
            display: block;
            margin: 0 auto;
        }
        
        .qr-label {
            font-size: 7px;
            text-align: center;
            opacity: 0.9;
            margin-top: 2px;
        }
        
        .badge-footer {
            width: 100%;
            padding-top: 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 8px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .department {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .validity {
            opacity: 0.8;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="badge-container">
        <!-- Header -->
        <div class="badge-header">
            <table class="header-table">
                <tr>
                    <td>
                        <div class="logo-text">GASPARD</div>
                        <div class="logo-sub">SIGNATURE</div>
                    </td>
                    <td class="badge-number">#{{ $badge->badge_number }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Body -->
        <div class="badge-body">
            <table class="body-table">
                <tr>
                    <td class="employee-info" width="*">
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
                    </td>
                    <td class="qr-section" width="60">
                        <div class="qr-code-wrapper">
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" />
                        </div>
                        <div class="qr-label">SCAN ME</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="badge-footer">
            <table class="footer-table">
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
