@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Statistiques et rapports',
        'actions' => '<a href="' . route('admin.statistics.export', ['format' => 'excel'] + request()->query()) . '" class="btn btn-success me-2">
                        <i class="fas fa-download"></i> Exporter Excel
                      </a>
                      <a href="' . route('admin.statistics.export', ['format' => 'pdf'] + request()->query()) . '" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exporter PDF
                      </a>'
    ])

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="module_id" class="form-label">Module</label>
                    <select class="form-select" id="module_id" name="module_id">
                        <option value="">Tous les modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>
                                {{ $module->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="group_id" class="form-label">Groupe</label>
                    <select class="form-select" id="group_id" name="group_id">
                        <option value="">Tous les groupes</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                    <a href="{{ route('admin.statistics.index') }}" class="btn btn-secondary ms-2">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">{{ number_format($statistics['overall']['sessions']) }}</h5>
                            <p class="card-text">Séances totales</p>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">{{ number_format($statistics['overall']['absences']) }}</h5>
                            <p class="card-text">Absences totales</p>
                        </div>
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">{{ $statistics['overall']['students'] }}</h5>
                            <p class="card-text">Étudiants</p>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">{{ $statistics['overall']['attendance_rate'] }}%</h5>
                            <p class="card-text">Taux de présence</p>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Module Statistics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques par module</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Séances</th>
                                    <th>Absences</th>
                                    <th>Taux présence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['module_stats']->take(10) as $moduleStat)
                                <tr>
                                    <td>{{ $moduleStat['module']->name }}</td>
                                    <td>{{ $moduleStat['sessions'] }}</td>
                                    <td>{{ $moduleStat['absences'] }}</td>
                                    <td>
                                        <span class="badge {{ $moduleStat['attendance_rate'] >= 80 ? 'bg-success' : ($moduleStat['attendance_rate'] >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $moduleStat['attendance_rate'] }}%
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

        <!-- Group Statistics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques par groupe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Séances</th>
                                    <th>Absences</th>
                                    <th>Taux présence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['group_stats']->take(10) as $groupStat)
                                <tr>
                                    <td>{{ $groupStat['group']->name }}</td>
                                    <td>{{ $groupStat['sessions'] }}</td>
                                    <td>{{ $groupStat['absences'] }}</td>
                                    <td>
                                        <span class="badge {{ $groupStat['attendance_rate'] >= 80 ? 'bg-success' : ($groupStat['attendance_rate'] >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $groupStat['attendance_rate'] }}%
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
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Étudiants les plus absents</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Groupe</th>
                                    <th>Nombre d'absences</th>
                                    <th>Taux d'absence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['top_absent_students']->take(20) as $student)
                                <tr>
                                    <td>{{ $student->user->name }}</td>
                                    <td>{{ $student->group ? $student->group->name : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $student->absences_count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $totalSessions = $statistics['overall']['sessions'];
                                            $absenceRate = $totalSessions > 0 ? ($student->absences_count / $totalSessions) * 100 : 0;
                                        @endphp
                                        <span class="badge {{ $absenceRate <= 20 ? 'bg-success' : ($absenceRate <= 30 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ number_format($absenceRate, 1) }}%
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

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Répartition des absences par module</h5>
                </div>
                <div class="card-body">
                    <canvas id="moduleAbsencesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Répartition des absences par groupe</h5>
                </div>
                <div class="card-body">
                    <canvas id="groupAbsencesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Évolution quotidienne (derniers 30 jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Chart Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Module Absences Chart (Pie Chart)
        const moduleCtx = document.getElementById('moduleAbsencesChart').getContext('2d');
        const moduleChart = new Chart(moduleCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($statistics['module_stats']->take(10) as $moduleStat)
                        '{{ $moduleStat['module']->name }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($statistics['module_stats']->take(10) as $moduleStat)
                            {{ $moduleStat['absences'] }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6B6B'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Group Absences Chart (Doughnut Chart)
        const groupCtx = document.getElementById('groupAbsencesChart').getContext('2d');
        const groupChart = new Chart(groupCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach($statistics['group_stats']->take(10) as $groupStat)
                        '{{ $groupStat['group']->name }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($statistics['group_stats']->take(10) as $groupStat)
                            {{ $groupStat['absences'] }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6B6B'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Daily Trend Chart (Line Chart)
        const dailyCtx = document.getElementById('dailyTrendChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($statistics['daily_trend']->take(30) as $day)
                        '{{ $day['date'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Taux de présence (%)',
                    data: [
                        @foreach($statistics['daily_trend']->take(30) as $day)
                            {{ $day['attendance_rate'] }},
                        @endforeach
                    ],
                    borderColor: '#4BC0C0',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Absences',
                    data: [
                        @foreach($statistics['daily_trend']->take(30) as $day)
                            {{ $day['absences'] }},
                        @endforeach
                    ],
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Auto-submit filters on change
        document.getElementById('module_id').addEventListener('change', function() {
            this.form.submit();
        });
        document.getElementById('group_id').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
@endsection
