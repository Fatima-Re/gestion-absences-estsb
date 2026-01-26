@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Détails du groupe',
        'actions' => '<a href="' . route('admin.groups.index') . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du groupe</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nom</dt>
                        <dd class="col-sm-9">{{ $group->name }}</dd>

                        <dt class="col-sm-3">Code</dt>
                        <dd class="col-sm-9">{{ $group->code }}</dd>

                        <dt class="col-sm-3">Niveau</dt>
                        <dd class="col-sm-9">{{ $group->level }}</dd>

                        <dt class="col-sm-3">Spécialité</dt>
                        <dd class="col-sm-9">{{ $group->specialty }}</dd>

                        <dt class="col-sm-3">Nombre max d'étudiants</dt>
                        <dd class="col-sm-9">{{ $group->max_students }}</dd>

                        <dt class="col-sm-3">Année académique</dt>
                        <dd class="col-sm-9">{{ $group->academic_year }}</dd>

                        <dt class="col-sm-3">Semestre</dt>
                        <dd class="col-sm-9">{{ $group->semester }}</dd>

                        <dt class="col-sm-3">Statut</dt>
                        <dd class="col-sm-9">
                            @include('partials.status-badges', ['isActive' => $group->is_active])
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Étudiants ({{ $group->students->count() }})</h5>
                    <a href="{{ route('admin.groups.students', $group) }}" class="btn btn-sm btn-primary">Gérer les étudiants</a>
                </div>
                <div class="card-body">
                    @if($group->students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Numéro étudiant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group->students->take(10) as $student)
                                <tr>
                                    <td>{{ $student->user->name }}</td>
                                    <td>{{ $student->user->email }}</td>
                                    <td>{{ $student->student_number }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($group->students->count() > 10)
                    <p class="text-muted mb-0">Et {{ $group->students->count() - 10 }} autres...</p>
                    @endif
                    @else
                    <p class="text-muted mb-0">Aucun étudiant dans ce groupe.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-warning w-100 mb-2">Modifier</a>
                    <a href="{{ route('admin.groups.students', $group) }}" class="btn btn-info w-100 mb-2">Gérer les étudiants</a>
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary w-100">Retour à la liste</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
