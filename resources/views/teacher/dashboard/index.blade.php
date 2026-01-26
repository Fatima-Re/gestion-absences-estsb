@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Tableau de bord Enseignant</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('teacher.schedule.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-alt me-1"></i>Emploi du temps
                    </a>
                    <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user-check me-1"></i>Présence
                    </a>
                    <a href="{{ route('teacher.modules.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-book me-1"></i>Mes modules
                    </a>
                    <a href="{{ route('teacher.reports.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-1"></i>Rapports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Modules</h5>
                    <h2 class="mb-0">{{ $stats['modules_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-2);">
                <div class="card-body">
                    <h5 class="card-title">Sessions ce mois</h5>
                    <h2 class="mb-0">{{ $stats['sessions_this_month'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white" style="background-color: var(--secondary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Présence en attente</h5>
                    <h2 class="mb-0">{{ $stats['pending_attendance'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Sessions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sessions d'aujourd'hui</h5>
                </div>
                <div class="card-body">
                    @if($todaySessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Groupe</th>
                                    <th>Heure</th>
                                    <th>Salle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySessions as $session)
                                <tr>
                                    <td>{{ $session->module->name }}</td>
                                    <td>{{ $session->group->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                    <td>{{ $session->room ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('teacher.attendance.create', $session) }}" class="btn btn-sm btn-primary">Prendre la présence</a>
                                        <a href="{{ route('teacher.schedule.show', $session) }}" class="btn btn-sm btn-secondary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucune session prévue aujourd'hui.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Sessions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Prochaines sessions</h5>
                </div>
                <div class="card-body">
                    @if($upcomingSessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Module</th>
                                    <th>Groupe</th>
                                    <th>Heure</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingSessions as $session)
                                <tr>
                                    <td>{{ $session->date->format('d/m/Y') }}</td>
                                    <td>{{ $session->module->name }}</td>
                                    <td>{{ $session->group->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                    <td>
                                        <a href="{{ route('teacher.schedule.show', $session) }}" class="btn btn-sm btn-primary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucune session à venir.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Absences -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Absences récentes</h5>
                </div>
                <div class="card-body">
                    @if($recentAbsences->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Module</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAbsences as $absence)
                                <tr>
                                    <td>{{ $absence->student->user->name }}</td>
                                    <td>{{ $absence->session->module->name ?? 'N/A' }}</td>
                                    <td>{{ $absence->session->date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($absence->status === 'justified')
                                            <span class="badge bg-success">Justifiée</span>
                                        @elseif($absence->status === 'pending')
                                            <span class="badge bg-warning">En attente</span>
                                        @else
                                            <span class="badge bg-danger">Non justifiée</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucune absence récente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
