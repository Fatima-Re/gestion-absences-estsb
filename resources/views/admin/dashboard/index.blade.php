@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Tableau de bord',
        'actions' => '
            <div class="btn-group me-2" role="group">
                <a href="' . route('admin.users.index') . '" class="btn btn-outline-primary">
                    <i class="fas fa-users me-1"></i>Utilisateurs
                </a>
                <a href="' . route('admin.groups.index') . '" class="btn btn-outline-secondary">
                    <i class="fas fa-users-cog me-1"></i>Groupes
                </a>
                <a href="' . route('admin.modules.index') . '" class="btn btn-outline-success">
                    <i class="fas fa-book me-1"></i>Modules
                </a>
                <a href="' . route('admin.sessions.index') . '" class="btn btn-outline-info">
                    <i class="fas fa-calendar-alt me-1"></i>Sessions
                </a>
                <a href="' . route('admin.statistics.index') . '" class="btn btn-outline-warning">
                    <i class="fas fa-chart-bar me-1"></i>Statistiques
                </a>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success" onclick="exportStudents()">
                    <i class="fas fa-file-excel me-1"></i>Exporter étudiants
                </button>
                <button type="button" class="btn btn-info" onclick="exportStatistics()">
                    <i class="fas fa-chart-line me-1"></i>Exporter stats
                </button>
            </div>
        '
    ])

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Étudiants</h5>
                    <h2 class="mb-0">{{ $stats['students_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-2);">
                <div class="card-body">
                    <h5 class="card-title">Enseignants</h5>
                    <h2 class="mb-0">{{ $stats['teachers_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--secondary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Groupes</h5>
                    <h2 class="mb-0">{{ $stats['groups_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--secondary-color-3);">
                <div class="card-body">
                    <h5 class="card-title">Modules</h5>
                    <h2 class="mb-0">{{ $stats['modules_count'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Absences aujourd'hui</h5>
                    <h3 class="mb-0">{{ $stats['absences_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Justifications en attente</h5>
                    <h3 class="mb-0">{{ $stats['pending_justifications'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sessions aujourd'hui</h5>
                    <h3 class="mb-0">{{ $stats['sessions_today'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Absences -->
    <div class="row mb-4">
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

    <!-- Pending Justifications -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Justifications en attente</h5>
                </div>
                <div class="card-body">
                    @if($pendingJustifications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Module</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingJustifications as $justification)
                                <tr>
                                    <td>{{ $justification->student->user->name }}</td>
                                    <td>{{ $justification->absence->session->module->name ?? 'N/A' }}</td>
                                    <td>{{ $justification->absence->session->date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.justifications.show', $justification) }}" class="btn btn-sm btn-primary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucune justification en attente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
