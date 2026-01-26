@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Tableau de bord Étudiant</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('student.schedule.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-alt me-1"></i>Emploi du temps
                    </a>
                    <a href="{{ route('student.absences.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user-times me-1"></i>Mes absences
                    </a>
                    <a href="{{ route('student.justifications.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-alt me-1"></i>Justifications
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Taux de présence</h5>
                    <h2 class="mb-0">{{ number_format($attendanceStats['attendance_rate'], 1) }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--primary-color-2);">
                <div class="card-body">
                    <h5 class="card-title">Absences totales</h5>
                    <h2 class="mb-0">{{ $attendanceStats['total_absences'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--secondary-color-1);">
                <div class="card-body">
                    <h5 class="card-title">Justifications en attente</h5>
                    <h2 class="mb-0">{{ $pendingJustifications }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background-color: var(--secondary-color-3);">
                <div class="card-body">
                    <h5 class="card-title">Notifications</h5>
                    <h2 class="mb-0">{{ $unreadNotifications }}</h2>
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
                                    <th>Heure</th>
                                    <th>Salle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySessions as $session)
                                <tr>
                                    <td>{{ $session->module->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                    <td>{{ $session->room ?? 'N/A' }}</td>
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
                                    <th>Module</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAbsences as $absence)
                                <tr>
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
                                    <td>
                                        @if($absence->isJustifiable() && !$absence->justification)
                                            <a href="{{ route('student.justifications.create', $absence) }}" class="btn btn-sm btn-primary">Justifier</a>
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
