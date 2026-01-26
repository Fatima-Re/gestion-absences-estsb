@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Gestion des séances',
        'actions' => '<a href="' . route('admin.sessions.create') . '" class="btn btn-primary">Planifier une séance</a>'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="date_filter" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_filter" name="date" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label for="module_filter" class="form-label">Module</label>
                    <select class="form-select" id="module_filter" name="module_id">
                        <option value="">Tous les modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="group_filter" class="form-label">Groupe</label>
                    <select class="form-select" id="group_filter" name="group_id">
                        <option value="">Tous les groupes</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="teacher_filter" class="form-label">Enseignant</label>
                    <select class="form-select" id="teacher_filter" name="teacher_id">
                        <option value="">Tous les enseignants</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Planifiée</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from_filter" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_from_filter" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to_filter" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_to_filter" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">Effacer les filtres</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Horaire</th>
                            <th>Module</th>
                            <th>Groupe</th>
                            <th>Enseignant</th>
                            <th>Salle</th>
                            <th>Statut</th>
                            <th>Présences</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session->date->format('d/m/Y') }}</td>
                            <td>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}</td>
                            <td>
                                <strong>{{ $session->module->code }}</strong><br>
                                <small class="text-muted">{{ $session->module->name }}</small>
                            </td>
                            <td>{{ $session->group->name }}</td>
                            <td>{{ $session->teacher->user->name }}</td>
                            <td>{{ $session->room }}</td>
                            <td>
                                @php
                                    $statusClass = match($session->status) {
                                        'scheduled' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    $statusText = match($session->status) {
                                        'scheduled' => 'Planifiée',
                                        'completed' => 'Terminée',
                                        'cancelled' => 'Annulée',
                                        default => ucfirst($session->status)
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                            <td>
                                {{ $session->attendanceRecords->count() }}/{{ $session->group->students->count() }}
                                @if($session->attendanceRecords->count() > 0)
                                    <br><small class="text-muted">{{ number_format(($session->attendanceRecords->where('status', 'present')->count() / $session->attendanceRecords->count()) * 100, 1) }}% présent</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($session->status === 'scheduled')
                                        <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.sessions.cancel', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr d\'annuler cette séance?');">
                                            @csrf
                                            <input type="hidden" name="reason" value="Annulation par l'administrateur">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de supprimer cette séance?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Aucune séance trouvée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sessions->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function clearFilters() {
    document.querySelectorAll('input[name], select[name]').forEach(element => {
        element.value = '';
    });
    window.location.href = '{{ route("admin.sessions.index") }}';
}

document.querySelectorAll('input[name], select[name]').forEach(element => {
    element.addEventListener('change', function() {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route("admin.sessions.index") }}';

        document.querySelectorAll('input[name], select[name]').forEach(el => {
            if (el.value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = el.name;
                input.value = el.value;
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endsection
