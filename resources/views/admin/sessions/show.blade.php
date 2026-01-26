@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails de la séance',
        'actions' => '
            <a href="' . route('admin.sessions.index') . '" class="btn btn-secondary">Retour à la liste</a>
            @if($session->status === "scheduled")
                <a href="' . route('admin.sessions.edit', $session) . '" class="btn btn-warning">Modifier</a>
            @endif
        '
    ])

    <div class="row">
        <!-- Session Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informations de la séance</h5>
                    <span class="badge bg-{{ $session->status === 'scheduled' ? 'primary' : ($session->status === 'completed' ? 'success' : 'danger') }}">
                        {{ $session->status === 'scheduled' ? 'Planifiée' : ($session->status === 'completed' ? 'Terminée' : 'Annulée') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Module</h6>
                            <p class="mb-2">
                                <strong>{{ $session->module->code }}</strong><br>
                                {{ $session->module->name }}
                            </p>

                            <h6 class="text-muted">Groupe</h6>
                            <p class="mb-2">{{ $session->group->name }}</p>

                            <h6 class="text-muted">Enseignant</h6>
                            <p class="mb-2">{{ $session->teacher->user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Date & Heure</h6>
                            <p class="mb-2">
                                <i class="fas fa-calendar me-2"></i>{{ $session->date->format('l d F Y') }}<br>
                                <i class="fas fa-clock me-2"></i>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                            </p>

                            <h6 class="text-muted">Salle</h6>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>{{ $session->room }}
                            </p>

                            @if($session->topic)
                                <h6 class="text-muted">Sujet</h6>
                                <p class="mb-2">{{ $session->topic }}</p>
                            @endif
                        </div>
                    </div>

                    @if($session->description)
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $session->description }}</p>
                    @endif

                    @if($session->status === 'cancelled' && $session->cancellation_reason)
                        <div class="alert alert-danger mt-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Raison de l'annulation</h6>
                            <p class="mb-0">{{ $session->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attendance Records -->
            @if($session->attendanceRecords->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Registres de présence ({{ $session->attendanceRecords->count() }}/{{ $session->group->students->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Statut</th>
                                        <th>Enregistré à</th>
                                        <th>Commentaire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($session->attendanceRecords as $record)
                                        <tr>
                                            <td>{{ $record->student->user->name }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($record->status) {
                                                        'present' => 'success',
                                                        'absent' => 'danger',
                                                        'late' => 'warning',
                                                        'excused' => 'info',
                                                        default => 'secondary'
                                                    };
                                                    $statusText = match($record->status) {
                                                        'present' => 'Présent',
                                                        'absent' => 'Absent',
                                                        'late' => 'Retard',
                                                        'excused' => 'Excusé',
                                                        default => ucfirst($record->status)
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                            </td>
                                            <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $record->comment ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card mt-4">
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Aucune présence enregistrée pour cette séance.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Statistiques rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $session->attendanceRecords->where('status', 'present')->count() }}</h4>
                                <small class="text-muted">Présents</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger">{{ $session->attendanceRecords->where('status', 'absent')->count() }}</h4>
                            <small class="text-muted">Absents</small>
                        </div>
                    </div>

                    @if($session->attendanceRecords->count() > 0)
                        <hr>
                        <div class="text-center">
                            <h5 class="text-success">
                                {{ number_format(($session->attendanceRecords->where('status', 'present')->count() / $session->attendanceRecords->count()) * 100, 1) }}%
                            </h5>
                            <small class="text-muted">Taux de présence</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($session->status === 'scheduled')
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-2"></i>Modifier la séance
                            </a>

                            <form action="{{ route('admin.sessions.cancel', $session) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr d\'annuler cette séance?');">
                                @csrf
                                <input type="hidden" name="reason" value="Annulation par l'administrateur">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-times me-2"></i>Annuler la séance
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Group Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Informations du groupe</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Groupe:</strong> {{ $session->group->name }}</p>
                    <p class="mb-1"><strong>Effectif:</strong> {{ $session->group->students->count() }} étudiants</p>
                    <p class="mb-0"><strong>Spécialité:</strong> {{ $session->group->specialty ?? 'Non spécifiée' }}</p>
                </div>
            </div>

            <!-- Module Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Informations du module</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Code:</strong> {{ $session->module->code }}</p>
                    <p class="mb-1"><strong>Crédits:</strong> {{ $session->module->credits }}</p>
                    <p class="mb-1"><strong>Semestre:</strong> {{ $session->module->semester }}</p>
                    <p class="mb-0"><strong>Année:</strong> {{ $session->module->academic_year }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
