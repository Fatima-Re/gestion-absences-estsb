<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de présence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            font-size: 16px;
        }
        .summary-box {
            display: inline-block;
            margin-right: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
        }
        .summary-box strong {
            display: block;
            font-size: 20px;
            color: #007bff;
        }
        .summary-box small {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Présence</h1>
        <p>Généré le {{ date('d/m/Y à H:i') }}</p>
    </div>

    @if(isset($statistics['module']))
        <div class="section">
            <h2>Informations du module</h2>
            <p>
                <strong>Module:</strong> {{ $statistics['module']['code'] }} - {{ $statistics['module']['name'] }}<br>
                <strong>Période:</strong> {{ $statistics['period']['from'] ?? 'N/A' }} à {{ $statistics['period']['to'] ?? 'N/A' }}
            </p>
        </div>
    @endif

    <div class="section">
        <h2>Résumé</h2>
        <div>
            <div class="summary-box">
                <strong>{{ $statistics['total_sessions'] ?? 0 }}</strong>
                <small>Séances totales</small>
            </div>
            <div class="summary-box">
                <strong>{{ $statistics['total_students'] ?? 0 }}</strong>
                <small>Étudiants concernés</small>
            </div>
            <div class="summary-box">
                <strong>{{ $statistics['total_absences'] ?? 0 }}</strong>
                <small>Absences totales</small>
            </div>
            <div class="summary-box">
                <strong>{{ number_format($statistics['overall_attendance_rate'] ?? 0, 1) }}%</strong>
                <small>Taux de présence</small>
            </div>
        </div>
    </div>

    @if(isset($statistics['group_statistics']) && count($statistics['group_statistics']) > 0)
        <div class="section">
            <h2>Statistiques par groupe</h2>
            <table>
                <thead>
                    <tr>
                        <th>Groupe</th>
                        <th>Effectif</th>
                        <th>Séances</th>
                        <th>Présences</th>
                        <th>Absences</th>
                        <th>Taux (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistics['group_statistics'] as $group)
                        <tr>
                            <td><strong>{{ $group['group']->name ?? 'N/A' }}</strong></td>
                            <td>{{ $group['students'] ?? 0 }}</td>
                            <td>{{ $group['sessions'] ?? 0 }}</td>
                            <td>{{ ($group['students'] ?? 0) * ($group['sessions'] ?? 0) - ($group['absences'] ?? 0) }}</td>
                            <td>{{ $group['absences'] ?? 0 }}</td>
                            <td>{{ number_format($group['attendance_rate'] ?? 0, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($include_comments && isset($statistics['comments']))
        <div class="section">
            <h2>Commentaires</h2>
            <p>{{ $statistics['comments'] }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Ce rapport a été généré automatiquement par le système de gestion des absences.</p>
    </div>
</body>
</html>
