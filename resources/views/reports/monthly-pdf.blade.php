<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Mensuel des Pointages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #074136;
            padding-bottom: 20px;
        }
        .logo {
            display: inline-block;
            background-color: #074136;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .logo-text {
            text-align: center;
            color: white;
        }
        .logo-main {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1.2;
            margin: 0;
        }
        .logo-sub {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            font-weight: normal;
            letter-spacing: 2px;
            margin-top: 3px;
            opacity: 0.95;
        }
        .header h1 {
            color: #074136;
            margin: 10px 0 0 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .info-section p {
            margin: 5px 0;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #074136;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        table th {
            background-color: #074136;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tfoot {
            background-color: #074136;
            color: white;
            font-weight: bold;
        }
        table tfoot td {
            border: 1px solid #074136;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <div class="logo-text">
                <div class="logo-main">GASPARD</div>
                <div class="logo-sub">SIGNATURE</div>
            </div>
        </div>
        <h1>Rapport Mensuel des Pointages</h1>
        @if($employee)
            <h2>{{ $employee->full_name }}</h2>
        @else
            <h2>Tous les employés</h2>
        @endif
    </div>

    <div class="info-section">
        <p><strong>Période:</strong> {{ $startDate->locale('fr')->isoFormat('DD MMMM YYYY') }} - {{ $endDate->locale('fr')->isoFormat('DD MMMM YYYY') }}</p>
        @if($employee)
            <p><strong>Employé:</strong> {{ $employee->full_name }} ({{ $employee->employee_code }})</p>
            <p><strong>Département:</strong> {{ $employee->department->name ?? 'N/A' }}</p>
        @else
            <p><strong>Employés:</strong> Tous</p>
        @endif
        <p><strong>Date de génération:</strong> {{ now()->locale('fr')->isoFormat('DD MMMM YYYY à HH:mm') }}</p>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <h3>Heures Totales</h3>
            <div class="value">{{ number_format($summary['total_hours'], 2) }}h</div>
        </div>
        <div class="summary-card">
            <h3>Heures Supplémentaires</h3>
            <div class="value">{{ number_format($summary['total_overtime_hours'], 2) }}h</div>
        </div>
        <div class="summary-card">
            <h3>Absences</h3>
            <div class="value">{{ $summary['total_absences'] }}</div>
        </div>
        <div class="summary-card">
            <h3>Jours de Repos</h3>
            <div class="value">{{ $summary['total_rest_days'] ?? 0 }}</div>
        </div>
    </div>

    <h3 style="color: #074136; margin-bottom: 10px;">Détails par jour</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                @if(!$employee)
                <th>Employé</th>
                <th>Code</th>
                <th>Département</th>
                @endif
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
                @if(!$employee)
                <td>{{ $record->employee->full_name }}</td>
                <td>{{ $record->employee->employee_code }}</td>
                <td>{{ $record->employee->department->name ?? '-' }}</td>
                @endif
                <td>
                    @if($record->check_in_time)
                        {{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($record->check_out_time)
                        {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                    @else
                        -
                    @endif
                </td>
                <td><strong>{{ number_format($record->total_minutes / 60, 2) }}h</strong></td>
                <td>
                    @php
                        $employeeId = $record->employee_id;
                        $monthlyOvertime = $monthlyOvertimeByEmployee[$employeeId] ?? 0;
                    @endphp
                    <strong>{{ number_format($monthlyOvertime, 2) }}h</strong>
                </td>
                <td>
                    @if($record->is_absent)
                        <span class="badge badge-danger">Absent</span>
                    @elseif($record->check_in_time && $record->check_out_time)
                        <span class="badge badge-success">Complet</span>
                    @elseif($record->check_in_time)
                        <span class="badge badge-info">En cours</span>
                    @else
                        <span class="badge badge-secondary">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $employee ? 9 : 12 }}" style="text-align: center; padding: 20px;">Aucun pointage enregistré pour cette période</td>
            </tr>
            @endforelse
        </tbody>
        @if($records->count() > 0)
        <tfoot>
            <tr>
                <td colspan="{{ $employee ? 3 : 6 }}"><strong>TOTAL</strong></td>
                <td><strong>{{ number_format($records->sum('total_minutes') / 60, 2) }}h</strong></td>
                <td><strong>{{ number_format($summary['total_overtime_hours'], 2) }}h</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Document généré le {{ now()->locale('fr')->isoFormat('DD MMMM YYYY à HH:mm') }} par GASPARD SIGNATURE</p>
    </div>
</body>
</html>

