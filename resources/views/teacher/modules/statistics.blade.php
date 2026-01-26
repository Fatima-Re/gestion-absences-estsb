@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Statistiques - ' . $module->name,
        'subtitle' => $module->code,
        'actions' => '
            <a href="' . route('teacher.modules.index') . '" class="btn btn-secondary">Retour aux modules</a>
            <a href="' . route('teacher.modules.show', $module) . '" class="btn btn-primary">Voir les détails</a>
        '
    ])

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-primary me-2" onclick="filterStatistics()">Filtrer</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">Réinitialiser</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $statistics['total_sessions'] }}</h3>
                    <p class="text-muted mb-0">Total des séances</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $statistics['total_students'] }}</h3>
                    <p class="text-muted mb-0">Étudiants inscrits</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $statistics['total_absences'] }}</h3>
                    <p class="text-muted mb-0">Total des absences</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ number_format($statistics['attendance_rate'], 1) }}%</h3>
                    <p class="text-muted mb-0">Taux de présence moyen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trend Chart -->
    @if(count($statistics['weekly_trend']) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Évolution hebdomadaire de la présence</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Attendance by Group -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Taux de présence par groupe</h5>
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
                                    <th>Évolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['attendance_by_group'] as $groupStat)
                                    <tr>
                                        <td>
                                            <strong>{{ $groupStat['group']->name }}</strong>
                                        </td>
                                        <td>{{ $groupStat['group']->students->count() }}</td>
                                        <td>{{ $groupStat['sessions'] }}</td>
                                        <td>{{ $groupStat['present'] }}</td>
                                        <td>{{ $groupStat['absent'] }}</td>
                                        <td>{{ $groupStat['late'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $groupStat['attendance_rate'] >= 80 ? 'success' : ($groupStat['attendance_rate'] >= 60 ? 'warning' : 'danger') }}">
                                                {{ number_format($groupStat['attendance_rate'], 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $change = rand(-5, 5); // Mock data - in real app, calculate from previous period
                                            @endphp
                                            <span class="text-{{ $change >= 0 ? 'success' : 'danger' }}">
                                                <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }}"></i>
                                                {{ abs($change) }}%
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($statistics['top_absent_students']->take(10) as $student)
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
                                            <td>
                                                <a href="{{ route('teacher.attendance.student', $student) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i> Détails
                                                </a>
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
    @endif

    <!-- Export Options -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Exporter les statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('teacher.reports.generate', $module) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                                <input type="hidden" name="format" value="pdf">
                                <input type="hidden" name="include_graphs" value="1">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-file-pdf me-2"></i>Exporter en PDF
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('teacher.reports.generate', $module) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                                <input type="hidden" name="format" value="excel">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Exporter en Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(count($statistics['weekly_trend']) > 0)
const weeklyData = @json($statistics['weekly_trend']);

const ctx = document.getElementById('weeklyTrendChart').getContext('2d');
const weeklyTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: weeklyData.map(item => `Semaine ${item.week}`),
        datasets: [{
            label: 'Taux de présence (%)',
            data: weeklyData.map(item => item.attendance_rate),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Évolution hebdomadaire du taux de présence'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
@endif

function filterStatistics() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;

    const url = new URL(window.location);
    url.searchParams.set('date_from', dateFrom);
    url.searchParams.set('date_to', dateTo);

    window.location.href = url.toString();
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');

    window.location.href = url.toString();
}
</script>
@endsection
