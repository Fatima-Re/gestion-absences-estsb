@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Gestion de la présence'
    ])

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
                                        @if($session->status === 'scheduled')
                                            <a href="{{ route('teacher.attendance.create', $session) }}" class="btn btn-sm btn-primary">Prendre la présence</a>
                                        @else
                                            <span class="text-muted">Présence déjà prise</span>
                                        @endif
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

    <!-- Recent Attendance Records -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Historique des présences</h5>
                </div>
                <div class="card-body">
                    @if($recentAttendance->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Module</th>
                                    <th>Groupe</th>
                                    <th>Présents</th>
                                    <th>Absents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendance as $record)
                                <tr>
                                    <td>{{ $record->session->date->format('d/m/Y') }}</td>
                                    <td>{{ $record->session->module->name }}</td>
                                    <td>{{ $record->session->group->name }}</td>
                                    <td>{{ $record->present_count }}</td>
                                    <td>{{ $record->absent_count }}</td>
                                    <td>
                                        <a href="{{ route('teacher.attendance.show', $record->session) }}" class="btn btn-sm btn-info">Voir détails</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucun historique de présence trouvé.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
