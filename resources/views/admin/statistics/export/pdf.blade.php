<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - {{ $statistics['period']['from'] }} à {{ $statistics['period']['to'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #055571;
            padding-bottom: 20px;
        }
        
        .school-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .school-logo {
            width: 120px;
            height: 80px;
            margin-right: 20px;
            object-fit: contain;
        }
        
        .school-info h1 {
            margin: 0;
            font-size: 24px;
            color: #055571;
            text-align: center;
            font-weight: bold;
        }
        
        .school-info p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #712105;
            text-align: center;
            font-weight: 500;
        }
        
        .period {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #055571;
        }
        
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
        }
        
        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #055571;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h2 {
            font-size: 20px;
            color: #333;
            border-bottom: 2px solid #055571;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 12px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #055571;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-success { background-color: #077aa2; color: white; }
        .badge-warning { background-color: #712105; color: white; }
        .badge-danger { background-color: #a7a29d; color: white; }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        
        @page {
            margin: 20px;
        }
        
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-header">
            <img src="{{ public_path('images/image_header.jpeg') }}" alt="School Logo" class="school-logo">
            <div class="school-info">
                <h1>École Supérieure de Technologie</h1>
                <p>Sidi Bennour - Maroc</p>
                <p>Service de la Scolarité</p>
            </div>
        </div>
        <div class="period">
            <strong>Période:</strong> {{ $statistics['period']['from'] }} à {{ $statistics['period']['to'] }}
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>Séances totales</h3>
            <div class="value">{{ $statistics['overall']['sessions'] }}</div>
        </div>
        <div class="summary-card">
            <h3>Absences totales</h3>
            <div class="value">{{ $statistics['overall']['absences'] }}</div>
        </div>
        <div class="summary-card">
            <h3>Étudiants</h3>
            <div class="value">{{ $statistics['overall']['students'] }}</div>
        </div>
        <div class="summary-card">
            <h3>Taux de présence</h3>
            <div class="value">{{ $statistics['overall']['attendance_rate'] }}%</div>
        </div>
    </div>

    <div class="section">
        <h2>Statistiques par Module</h2>
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Séances</th>
                    <th>Absences</th>
                    <th>Étudiants</th>
                    <th>Taux de présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['module_stats'] as $moduleStat)
                <tr>
                    <td>{{ $moduleStat['module']->name }}</td>
                    <td>{{ $moduleStat['module']->code }}</td>
                    <td>{{ $moduleStat['sessions'] }}</td>
                    <td>{{ $moduleStat['absences'] }}</td>
                    <td>{{ $moduleStat['students'] }}</td>
                    <td>
                        <span class="badge {{ $moduleStat['attendance_rate'] >= 80 ? 'badge-success' : ($moduleStat['attendance_rate'] >= 60 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $moduleStat['attendance_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Statistiques par Groupe</h2>
        <table>
            <thead>
                <tr>
                    <th>Groupe</th>
                    <th>Séances</th>
                    <th>Absences</th>
                    <th>Étudiants</th>
                    <th>Taux de présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['group_stats'] as $groupStat)
                <tr>
                    <td>{{ $groupStat['group']->name }}</td>
                    <td>{{ $groupStat['sessions'] }}</td>
                    <td>{{ $groupStat['absences'] }}</td>
                    <td>{{ $groupStat['students'] }}</td>
                    <td>
                        <span class="badge {{ $groupStat['attendance_rate'] >= 80 ? 'badge-success' : ($groupStat['attendance_rate'] >= 60 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $groupStat['attendance_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top Étudiants Absents</h2>
        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Numéro étudiant</th>
                    <th>Groupe</th>
                    <th>Absences</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['top_absent_students'] as $student)
                <tr>
                    <td>{{ $student->user->name ?? 'N/A' }}</td>
                    <td>{{ $student->student_number ?? 'N/A' }}</td>
                    <td>{{ $student->group->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-danger">{{ $student->absences_count ?? 0 }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Tendance Journalière</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Séances</th>
                    <th>Absences</th>
                    <th>Taux de présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['daily_trend'] as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}</td>
                    <td>{{ $day['sessions'] }}</td>
                    <td>{{ $day['absences'] }}</td>
                    <td>
                        <span class="badge {{ $day['attendance_rate'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                            {{ $day['attendance_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Tendance Hebdomadaire</h2>
        <table>
            <thead>
                <tr>
                    <th>Semaine</th>
                    <th>Séances</th>
                    <th>Absences</th>
                    <th>Taux de présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['weekly_trend'] as $week)
                <tr>
                    <td>Semaine {{ $week['week'] }}</td>
                    <td>{{ $week['sessions'] }}</td>
                    <td>{{ $week['absences'] }}</td>
                    <td>
                        <span class="badge {{ $week['attendance_rate'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                            {{ $week['attendance_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y H:i') }}</p>
        <p>Ce document est officiel et a été généré par le système de gestion des absences</p>
    </div>
</body>
</html>