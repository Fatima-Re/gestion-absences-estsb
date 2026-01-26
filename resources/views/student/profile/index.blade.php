@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mon profil',
        'actions' => '<a href="' . route('student.profile.edit') . '" class="btn btn-primary">Modifier le profil</a>'
    ])

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($student->user->avatar)
                            <img src="{{ asset('storage/' . $student->user->avatar) }}" alt="Avatar" class="rounded-circle" width="100" height="100">
                        @else
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2rem;">
                                {{ strtoupper(substr($student->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h5 class="card-title">{{ $student->user->name }}</h5>
                    <p class="text-muted">{{ $student->user->email }}</p>
                    @include('partials.status-badges', ['role' => 'student'])
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Nom complet</h6>
                            <p class="mb-3">{{ $student->user->name }}</p>

                            <h6>Email</h6>
                            <p class="mb-3">{{ $student->user->email }}</p>

                            <h6>Téléphone</h6>
                            <p class="mb-3">{{ $student->user->phone ?? 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Groupe</h6>
                            <p class="mb-3">{{ $student->group->name ?? 'Non assigné' }}</p>

                            <h6>Numéro étudiant</h6>
                            <p class="mb-3">{{ $student->student_number ?? 'N/A' }}</p>

                            <h6>Date d'inscription</h6>
                            <p class="mb-3">{{ $student->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary">{{ $stats['total_sessions'] }}</h3>
                            <small class="text-muted">Sessions totales</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success">{{ $stats['present_sessions'] }}</h3>
                            <small class="text-muted">Présences</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ $stats['absent_sessions'] }}</h3>
                            <small class="text-muted">Absences</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ number_format($stats['attendance_rate'], 1) }}%</h3>
                            <small class="text-muted">Taux de présence</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
