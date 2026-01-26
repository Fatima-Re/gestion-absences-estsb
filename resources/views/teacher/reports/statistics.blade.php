@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Aperçu du rapport - ' . $statistics['module']['name'],
        'subtitle' => $statistics['module']['code'] . ' | ' . $statistics['period']['from'] . ' - ' . $statistics['period']['to'],
        'actions' => '
            <a href="' . route('teacher.reports.index') . '" class="btn btn-secondary">Retour aux rapports</a>
            <a href="' . route('teacher.modules.show', $statistics['module']['id']) . '" class="btn btn-primary">Voir le module</a>
        '
    ])

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $statistics['total_sessions'] }}</h3>
                    <p class="text-muted mb-0">Séances totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $statistics['total_students'] }}</h3>
                    <p class="text-muted mb-0">Étudiants concernés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $statistics['total_absences'] }}</h3>
                    <p class="text-muted mb-0">Absences totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ number_format($statistics['overall_attendance_rate'], 1) }}%</h3>
                    <p class="text-muted mb-0">Taux de présence global</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques par groupe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Effectif</th>
                                    <th>Séances</th>
                                    <th>Présences</th>
                                    <th>Absences</th>
                                    <th>Retards</th>
                                    <th>Taux de présence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['group_statistics'] as $group)
                                    <tr>
                                        <td>
                                            <strong>{{ $group['group']->name }}</strong>
                                        </td>
                                        <td>{{ $group['students'] }}</td>
                                        <td>{{ $group['sessions'] }}</td>
                                        <td>{{ $group['present'] }}</td>
                                        <td>{{ $group['absent'] }}</td>
                                        <td>{{ $group['late'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $group['attendance_rate'] >= 80 ? 'success' : ($group['attendance_rate'] >= 60 ? 'warning' : 'danger') }}">
                                                {{ number_format($group['attendance_rate'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Attendance Trend -->
    @if(count($statistics['daily_trend']) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Évolution quotidienne de la présence</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Top Absent Students -->
    @if(count($statistics['top_absent_students']) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Étudiants les plus absents</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Groupe</th>
                                        <th>Nombre d'absences</th>
                                        <th>Taux de présence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['top_absent_students']->take(15) as $student)
                                    <tr>
                                        <td>{{ $student->user->name }}</td>
                                        <td>{{ $student->group->name }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $student->absences_count }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $studentSessions = $statistics['total_sessions'];
                                                $studentAttendanceRate = $studentSessions > 0 ? (($studentSessions - $student->absences_count) / $studentSessions) * 100 : 100;
                                            @endphp
                                            <span class="badge bg-{{ $studentAttendanceRate >= 80 ? 'success' : ($studentAttendanceRate >= 60 ? 'warning' : 'danger') }}">
                                                {{ number_format($studentAttendanceRate, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Report Generation Options -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Générer le rapport</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Prévisualisation :</strong> Ceci est un aperçu des données qui seront incluses dans votre rapport. Cliquez sur l'un des boutons ci-dessous pour générer et télécharger le rapport.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h5>Rapport PDF</h5>
                                    <p class="text-muted">Rapport formaté avec graphiques et statistiques détaillées</p>
                                    <form action="{{ route('teacher.reports.generate', $statistics['module']['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="date_from" value="{{ $statistics['period']['from'] }}">
                                        <input type="hidden" name="date_to" value="{{ $statistics['period']['to'] }}">
                                        <input type="hidden" name="format" value="pdf">
                                        <input type="hidden" name="include_graphs" value="1">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-download me-2"></i>Télécharger PDF
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                    <h5>Rapport Excel</h5>
                                    <p class="text-muted">Données brutes exportables pour analyse approfondie</p>
                                    <form action="{{ route('teacher.reports.generate', $statistics['module']['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="date_from" value="{{ $statistics['period']['from'] }}">
                                        <input type="hidden" name="date_to" value="{{ $statistics['period']['to'] }}">
                                        <input type="hidden" name="format" value="excel">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-download me-2"></i>Télécharger Excel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Rapport généré le {{ now()->format('d/m/Y à H:i') }} pour le module {{ $statistics['module']['code'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(count($statistics['daily_trend']) > 0)
const dailyData = @json($statistics['daily_trend']);

const ctx = document.getElementById('dailyTrendChart').getContext('2d');
const dailyTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => new Date(item.date).toLocaleDateString('fr-FR')),
        datasets: [{
            label: 'Taux de présence (%)',
            data: dailyData.map(item => item.attendance_rate),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1,
            pointRadius: 3,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Évolution quotidienne du taux de présence'
            },
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Taux de présence (%)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
@endif
</script>
@endsection
