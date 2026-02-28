@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Statistiques de présence',
        'subtitle' => 'Analyse détaillée de votre présence par module'
    ])

    @if(empty($statistics) || count($statistics) === 0)
        <div class="alert alert-info">
            <p>Aucune donnée de présence disponible pour le moment.</p>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Présence par module</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th class="text-center">Séances totales</th>
                                        <th class="text-center">Absences justifiées</th>
                                        <th class="text-center">Absences non justifiées</th>
                                        <th class="text-center">Total absences</th>
                                        <th class="text-center">Taux de présence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($statistics as $stat)
                                        <tr>
                                            <td>
                                                <strong>{{ $stat['module']->code ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $stat['module']->name ?? '' }}</small>
                                            </td>
                                            <td class="text-center">{{ $stat['total_sessions'] }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $stat['justified_absences'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $stat['unjustified_absences'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $stat['absences'] }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 24px; min-width: 150px;">
                                                    @php
                                                        $rate = $stat['attendance_rate'];
                                                        $color = $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger');
                                                    @endphp
                                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                         style="width: {{ $rate }}%" 
                                                         aria-valuenow="{{ $rate }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ number_format($rate, 1) }}%
                                                    </div>
                                                </div>
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

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">{{ array_sum(array_column($statistics, 'total_sessions')) }}</h3>
                        <p class="text-muted mb-0">Séances totales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">{{ array_sum(array_column($statistics, 'justified_absences')) }}</h3>
                        <p class="text-muted mb-0">Absences justifiées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger">{{ array_sum(array_column($statistics, 'unjustified_absences')) }}</h3>
                        <p class="text-muted mb-0">Absences non justifiées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        @php
                            $totalSessions = array_sum(array_column($statistics, 'total_sessions'));
                            $totalAbsences = array_sum(array_column($statistics, 'absences'));
                            $overallRate = $totalSessions > 0 ? (($totalSessions - $totalAbsences) / $totalSessions) * 100 : 100;
                        @endphp
                        <h3 class="text-{{ $overallRate >= 90 ? 'success' : ($overallRate >= 75 ? 'warning' : 'danger') }}">
                            {{ number_format($overallRate, 1) }}%
                        </h3>
                        <p class="text-muted mb-0">Taux global de présence</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('student.absences.index') }}" class="btn btn-secondary">Retour aux absences</a>
                <a href="{{ route('student.absences.report') }}" class="btn btn-primary">Télécharger le rapport</a>
            </div>
        </div>
    @endif
</div>
@endsection
