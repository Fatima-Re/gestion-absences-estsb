@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails du module',
        'actions' => '<a href="' . route('admin.modules.index') . '" class="btn btn-secondary me-2">Retour</a>
                     <a href="' . route('admin.modules.edit', $module) . '" class="btn btn-warning me-2">Modifier</a>
                     <form action="' . route('admin.modules.destroy', $module) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Êtes-vous sûr?\');">
                         @csrf
                         @method(\'DELETE\')
                         <button type="submit" class="btn btn-danger">Supprimer</button>
                     </form>'
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du module</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Code</h6>
                            <p class="mb-3">{{ $module->code }}</p>

                            <h6>Nom</h6>
                            <p class="mb-3">{{ $module->name }}</p>

                            <h6>Crédits</h6>
                            <p class="mb-3">{{ $module->credits }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Semestre</h6>
                            <p class="mb-3">Semestre {{ $module->semester }}</p>

                            <h6>Enseignant</h6>
                            <p class="mb-3">{{ $module->teacher->user->name ?? 'Non assigné' }}</p>

                            <h6>Statut</h6>
                            <p class="mb-3">
                                @include('partials.status-badges', ['isActive' => $module->is_active])
                            </p>
                        </div>
                    </div>

                    @if($module->description)
                    <div class="row">
                        <div class="col-12">
                            <h6>Description</h6>
                            <p>{{ $module->description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary">{{ $module->groups->count() }}</h3>
                            <small class="text-muted">Groupes</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">{{ $module->sessions->count() }}</h3>
                            <small class="text-muted">Sessions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups assigned to this module -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Groupes assignés</h5>
                </div>
                <div class="card-body">
                    @if($module->groups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Code</th>
                                    <th>Niveau</th>
                                    <th>Spécialité</th>
                                    <th>Étudiants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($module->groups as $group)
                                <tr>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->code }}</td>
                                    <td>{{ $group->level }}</td>
                                    <td>{{ $group->specialty }}</td>
                                    <td>{{ $group->students->count() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucun groupe assigné à ce module.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent sessions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sessions récentes</h5>
                </div>
                <div class="card-body">
                    @if($module->sessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Groupe</th>
                                    <th>Heure</th>
                                    <th>Salle</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($module->sessions->take(10) as $session)
                                <tr>
                                    <td>{{ $session->date->format('d/m/Y') }}</td>
                                    <td>{{ $session->group->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                    <td>{{ $session->room ?? 'N/A' }}</td>
                                    <td>
                                        @include('partials.status-badges', ['sessionStatus' => $session->status])
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">Aucune session trouvée pour ce module.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
