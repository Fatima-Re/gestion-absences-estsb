@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Gestion des absences',
        'actions' => '<button type="button" class="btn btn-success" onclick="exportAbsences({module_id: \'' . request('module_id') . '\', group_id: \'' . request('group_id') . '\', status: \'' . request('status') . '\', date: \'' . request('date') . '\'})"><i class="fas fa-file-excel me-1"></i>Exporter Excel</button>'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
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
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="justified" {{ request('status') == 'justified' ? 'selected' : '' }}>Justifiées</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="unjustified" {{ request('status') == 'unjustified' ? 'selected' : '' }}>Non justifiées</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_filter" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_filter" name="date" value="{{ request('date') }}">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Module</th>
                            <th>Groupe</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absences as $absence)
                        <tr>
                            <td>{{ $absence->student->user->name }}</td>
                            <td>{{ $absence->session->module->name ?? 'N/A' }}</td>
                            <td>{{ $absence->session->group->name ?? 'N/A' }}</td>
                            <td>{{ $absence->session->date->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($absence->session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($absence->session->end_time)->format('H:i') }}</td>
                            <td>
                                @include('partials.status-badges', ['status' => $absence->status])
                            </td>
                            <td>
                                <a href="{{ route('admin.absences.show', $absence) }}" class="btn btn-sm btn-info">Voir</a>
                                @if($absence->status === 'pending')
                                    <form action="{{ route('admin.absences.update', $absence) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="justified">
                                        <button type="submit" class="btn btn-sm btn-success">Justifier</button>
                                    </form>
                                    <form action="{{ route('admin.absences.update', $absence) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="unjustified">
                                        <button type="submit" class="btn btn-sm btn-danger">Non justifier</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune absence trouvée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $absences->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('module_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('group_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('status_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('date_filter').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
