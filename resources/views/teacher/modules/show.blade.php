@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => $module->name,
        'subtitle' => $module->code,
        'actions' => '
            <a href="' . route('teacher.modules.index') . '" class="btn btn-secondary">Retour à la liste</a>
            <a href="' . route('teacher.modules.statistics', $module) . '" class="btn btn-info">Voir les statistiques</a>
        '
    ])

    <!-- Module Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du module</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Code</h6>
                            <p class="mb-2">{{ $module->code }}</p>

                            <h6 class="text-muted">Nom</h6>
                            <p class="mb-2">{{ $module->name }}</p>

                            <h6 class="text-muted">Semestre</h6>
                            <p class="mb-2">Semestre {{ $module->semester }}</p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Crédits</h6>
                            <p class="mb-2">{{ $module->credits }}</p>

                            <h6 class="text-muted">Heures</h6>
                            <p class="mb-2">{{ $module->hours }} heures</p>

                            <h6 class="text-muted">Année académique</h6>
                            <p class="mb-2">{{ $module->academic_year }}</p>
                        </div>
                    </div>

                    @if($module->description)
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $module->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Statistiques rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $statistics['total_sessions'] }}</h4>
                                <small class="text-muted">Sessions</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ $statistics['total_students'] }}</h4>
                            <small class="text-muted">Étudiants</small>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-danger">{{ $statistics['total_absences'] }}</h4>
                                <small class="text-muted">Absences</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ number_format($statistics['attendance_rate'], 1) }}%</h4>
                            <small class="text-muted">Présence</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups and Sessions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Groupes et séances</h5>
                </div>
                <div class="card-body">
                    <!-- Date Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary me-2" onclick="filterSessions()">Filtrer</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">Réinitialiser</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Horaire</th>
                                    <th>Groupe</th>
                                    <th>Salle</th>
                                    <th>Sujet</th>
                                    <th>Statut</th>
                                    <th>Présences</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>{{ $session->date->format('d/m/Y') }}</td>
                                        <td>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}</td>
                                        <td>{{ $session->group->name }}</td>
                                        <td>{{ $session->room }}</td>
                                        <td>{{ $session->topic ?: '-' }}</td>
                                        <td>
                                            @php
                                                $statusClass = match($session->status) {
                                                    'scheduled' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                                $statusText = match($session->status) {
                                                    'scheduled' => 'Planifiée',
                                                    'completed' => 'Terminée',
                                                    'cancelled' => 'Annulée',
                                                    default => ucfirst($session->status)
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            {{ $session->attendanceRecords->count() }}/{{ $session->group->students->count() }}
                                            @if($session->attendanceRecords->count() > 0)
                                                <br><small class="text-muted">{{ number_format(($session->attendanceRecords->where('status', 'present')->count() / $session->attendanceRecords->count()) * 100, 1) }}% présent</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('teacher.attendance.show', $session) }}" class="btn btn-sm btn-info" title="Voir les présences">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Aucune séance trouvée pour cette période.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance by Group -->
    @if(count($statistics['attendance_by_group']) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Taux de présence par groupe</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($statistics['attendance_by_group'] as $groupStat)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">{{ $groupStat['group']->name }}</h6>
                                            <h4 class="text-primary">{{ number_format($groupStat['attendance_rate'], 1) }}%</h4>
                                            <small class="text-muted">
                                                {{ $groupStat['absences'] }} absence(s) sur {{ $groupStat['sessions'] }} séance(s)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function filterSessions() {
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
