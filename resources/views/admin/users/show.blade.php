@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Détails de l\'utilisateur',
        'actions' => '<a href="' . route('admin.users.index') . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nom complet</dt>
                        <dd class="col-sm-9">{{ $user->name }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $user->email }}</dd>

                        <dt class="col-sm-3">Téléphone</dt>
                        <dd class="col-sm-9">{{ $user->phone ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Rôle</dt>
                        <dd class="col-sm-9">
                            @include('partials.status-badges', ['role' => $user->role])
                        </dd>

                        <dt class="col-sm-3">Statut</dt>
                        <dd class="col-sm-9">
                            @include('partials.status-badges', ['isActive' => $user->is_active])
                        </dd>
                    </dl>
                </div>
            </div>

            @if($user->role === 'student' && $user->student)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Informations étudiant</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Numéro étudiant</dt>
                        <dd class="col-sm-9">{{ $user->student->student_number }}</dd>

                        <dt class="col-sm-3">Groupe</dt>
                        <dd class="col-sm-9">{{ $user->student->group->name ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>
            @endif

            @if($user->role === 'teacher' && $user->teacher)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Informations enseignant</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Code enseignant</dt>
                        <dd class="col-sm-9">{{ $user->teacher->teacher_code }}</dd>

                        <dt class="col-sm-3">Spécialisation</dt>
                        <dd class="col-sm-9">{{ $user->teacher->specialization ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning w-100 mb-2">Modifier</a>
                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $user->is_active ? 'secondary' : 'success' }} w-100">
                            {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Réinitialiser le mot de passe?');">
                        @csrf
                        <button type="submit" class="btn btn-info w-100 mb-2">Réinitialiser mot de passe</button>
                    </form>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-100">Retour à la liste</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
