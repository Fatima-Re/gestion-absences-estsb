@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Gestion des groupes',
        'actions' => '<a href="' . route('admin.groups.create') . '" class="btn btn-primary">Ajouter un groupe</a>'
    ])

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Niveau</th>
                            <th>Spécialité</th>
                            <th>Étudiants</th>
                            <th>Année académique</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->code }}</td>
                            <td>{{ $group->level }}</td>
                            <td>{{ $group->specialty }}</td>
                            <td>{{ $group->active_students_count ?? 0 }} / {{ $group->max_students }}</td>
                            <td>{{ $group->academic_year }}</td>
                            <td>
                                @include('partials.status-badges', ['isActive' => $group->is_active])
                            </td>
                            <td>
                                <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <form action="{{ route('admin.groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Aucun groupe trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $groups->links() }}
        </div>
    </div>
</div>
@endsection
