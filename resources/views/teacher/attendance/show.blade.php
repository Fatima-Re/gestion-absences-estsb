@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails de la présence',
        'actions' => '<a href="' . route('teacher.attendance.index') . '" class="btn btn-secondary me-2">Retour</a>' .
                     ($session->canBeModified() ? '<a href="' . route('teacher.attendance.edit', $session) . '" class="btn btn-warning">Modifier</a>' : '')
    ])

    <div class="row">
        <div class="col-md-8">
            <!-- Session Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la séance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Module</h6>
                            <p class="mb-3">{{ $session->module->name }}</p>

                            <h6>Groupe</h6>
                            <p class="mb-3">{{ $session->group->name }}</p>

                            <h6>Statut de la séance</h6>
                            <p class="mb-3">
                                @include('partials.status-badges', ['sessionStatus' => $session->status])
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Date</h6>
                            <p class="mb-3">{{ $session->date->format('d/m/Y') }}</p>

                            <h6>Heure</h6>
                            <p class="mb-3">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</p>

                            <h6>Salle</h6>
                            <p class="mb-3">{{ $session->room ?? 'Non spécifiée' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Résumé de la présence</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-success text-white rounded">
                                <h4 class="mb-1">{{ $attendanceRecords->where('status', 'present')->count() }}</h4>
                                <small>Présents</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-danger text-white rounded">
                                <h4 class="mb-1">{{ $attendanceRecords->where('status', 'absent')->count() }}</h4>
                                <small>Absents</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-warning text-white rounded">
                                <h4 class="mb-1">{{ $attendanceRecords->where('status', 'excused')->count() }}</h4>
                                <small>Excusés</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-info text-white rounded">
                                <h4 class="mb-1">{{ $attendanceRecords->where('status', 'late')->count() }}</h4>
                                <small>En retard</small>
                            </div>
                        </div>
                    </div>

                    @if($attendanceRecords->where('status', 'late')->count() > 0)
                    <div class="mt-3">
                        <h6>Retards détaillés:</h6>
                        <ul>
                            @foreach($attendanceRecords->where('status', 'late') as $record)
                            <li>{{ $record->student->user->name }}: {{ $record->late_minutes }} minutes</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Attendance Records -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Feuille de présence</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                        @foreach($attendanceRecords as $record)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $record->student->user->name }}</strong>
                                    @if($record->student->student_number)
                                        <br><small class="text-muted">{{ $record->student->student_number }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    @if($record->status === 'present')
                                        <span class="badge bg-success">Présent</span>
                                    @elseif($record->status === 'absent')
                                        <span class="badge bg-danger">Absent</span>
                                    @elseif($record->status === 'excused')
                                        <span class="badge bg-warning">Excusé</span>
                                    @elseif($record->status === 'late')
                                        <span class="badge bg-info">Retard ({{ $record->late_minutes }}min)</span>
                                    @endif
                                </div>
                            </div>
                            @if($record->comments)
                                <div class="mt-2">
                                    <small class="text-muted"><i class="fas fa-comment"></i> {{ $record->comments }}</small>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Modification Info -->
            @if($session->attendanceRecords()->exists())
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Informations de modification</h5>
                </div>
                <div class="card-body">
                    <p><strong>Enregistré par:</strong> {{ $attendanceRecords->first()->recordedBy->user->name ?? 'Système' }}</p>
                    <p><strong>Date d'enregistrement:</strong> {{ $attendanceRecords->first()->recorded_at->format('d/m/Y H:i') }}</p>

                    @if(!$session->canBeModified())
                    <div class="alert alert-info mt-3">
                        <small>La période de modification (48h) est expirée. Contactez l'administration pour toute modification.</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
