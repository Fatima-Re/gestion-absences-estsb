@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Gestion des justifications'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetées</option>
                        <option value="needs_info" {{ request('status') == 'needs_info' ? 'selected' : '' }}>Informations requises</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student_filter" class="form-label">Étudiant</label>
                    <input type="text" class="form-control" id="student_filter" name="student" value="{{ request('student') }}" placeholder="Nom de l'étudiant">
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
                            <th>Raison</th>
                            <th>Date de soumission</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($justifications as $justification)
                        <tr>
                            <td>{{ $justification->absence->student->user->name }}</td>
                            <td>{{ $justification->absence->session->module->name ?? 'N/A' }}</td>
                            <td>{{ $justification->reason }}</td>
                            <td>{{ $justification->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @include('partials.status-badges', ['justificationStatus' => $justification->status])
                            </td>
                            <td>
                                <a href="{{ route('admin.justifications.show', $justification) }}" class="btn btn-sm btn-info">Voir</a>
                                @if($justification->status === 'pending')
                                    <form action="{{ route('admin.justifications.update', $justification) }}" method="POST" class="d-inline me-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success">Approuver</button>
                                    </form>
                                    <form action="{{ route('admin.justifications.update', $justification) }}" method="POST" class="d-inline me-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">Rejeter</button>
                                    </form>
                                    <form action="{{ route('admin.justifications.update', $justification) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="needs_info">
                                        <button type="submit" class="btn btn-sm btn-warning">Demander info</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucune justification trouvée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $justifications->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('status_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('student_filter').addEventListener('input', function() {
    // Debounce search
    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
document.getElementById('module_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('date_filter').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
