@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Gestion des modules',
        'actions' => '<a href="' . route('admin.modules.create') . '" class="btn btn-primary">Ajouter un module</a>'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="name_filter" class="form-label">Nom du module</label>
                    <input type="text" class="form-control" id="name_filter" name="name" value="{{ request('name') }}" placeholder="Rechercher par nom">
                </div>
                <div class="col-md-4">
                    <label for="teacher_filter" class="form-label">Enseignant</label>
                    <select class="form-select" id="teacher_filter" name="teacher_id">
                        <option value="">Tous les enseignants</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="semester_filter" class="form-label">Semestre</label>
                    <select class="form-select" id="semester_filter" name="semester">
                        <option value="">Tous les semestres</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Semestre 1</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Semestre 2</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Enseignant</th>
                            <th>Crédits</th>
                            <th>Semestre</th>
                            <th>Groupes</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                        <tr>
                            <td>{{ $module->code }}</td>
                            <td>{{ $module->name }}</td>
                            <td>{{ $module->teacher->user->name ?? 'Non assigné' }}</td>
                            <td>{{ $module->credits }}</td>
                            <td>Semestre {{ $module->semester }}</td>
                            <td>{{ $module->groups->count() }}</td>
                            <td>
                                @include('partials.status-badges', ['isActive' => $module->is_active])
                            </td>
                            <td>
                                <a href="{{ route('admin.modules.show', $module) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <form action="{{ route('admin.modules.destroy', $module) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Aucun module trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $modules->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('name_filter').addEventListener('input', function() {
    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
document.getElementById('teacher_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('semester_filter').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
