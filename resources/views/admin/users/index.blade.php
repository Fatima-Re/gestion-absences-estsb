@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Gestion des utilisateurs',
        'actions' => '<a href="' . route('admin.users.create') . '" class="btn btn-primary">Ajouter un utilisateur</a>'
    ])

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="role" class="form-label">Type d'utilisateur</label>
                    <select name="role" id="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les utilisateurs</option>
                        <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Étudiants</option>
                        <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Enseignants</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrateurs</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Nom ou email..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            Chercher
                        </button>
                        @if(request()->hasAny(['role', 'status', 'search']))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Réinitialiser les filtres">
                                Réinitialiser
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary" title="Importer des étudiants">
                            Importer
                        </a>
                        <a href="{{ route('admin.import.export-students') }}" class="btn btn-outline-secondary" title="Exporter les étudiants">
                            Exporter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    @if(request()->hasAny(['role', 'status', 'search']))
                        <span class="badge bg-primary">{{ $users->total() }} résultat(s)</span>
                        @if(request('role'))
                            <span class="badge bg-info">{{ ucfirst(request('role')) }}s</span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-{{ request('status') === 'active' ? 'success' : 'warning' }}">
                                {{ request('status') === 'active' ? 'Actifs' : 'Inactifs' }}
                            </span>
                        @endif
                        @if(request('search'))
                            <span class="badge bg-secondary">Recherche: "{{ request('search') }}"</span>
                        @endif
                    @else
                        <span class="badge bg-primary">{{ $users->total() }} utilisateur(s) au total</span>
                    @endif
                </div>
                <div>
                    <span class="badge bg-success">{{ $users->total() }} actif(s)</span>
                    <span class="badge bg-warning">{{ \App\Models\User::where('is_active', false)->count() }} inactif(s)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Détails</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @include('partials.status-badges', ['role' => $user->role])
                            </td>
                            <td>
                                @if($user->role === 'student' && $user->student)
                                    <small class="text-muted">{{ $user->student->filiere }}</small>
                                @elseif($user->role === 'teacher' && $user->teacher)
                                    <small class="text-muted">{{ $user->teacher->departement ?? '-' }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                @include('partials.status-badges', ['isActive' => $user->is_active])
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur {{ $user->name }} ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5>Aucun utilisateur trouvé</h5>
                                    <p class="text-muted">
                                        @if(request()->hasAny(['role', 'status', 'search']))
                                            Aucun utilisateur correspondant à vos critères de recherche.
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-link p-0">Réinitialiser les filtres</a>
                                        @else
                                            Aucun utilisateur enregistré pour le moment.
                                            <a href="{{ route('admin.users.create') }}" class="btn btn-link p-0">Ajouter un utilisateur</a>
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Add some custom styles for better UX -->
<style>
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    
    .badge {
        font-size: 0.875rem;
        margin-right: 0.25rem;
    }
    
    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
</style>
@endsection
