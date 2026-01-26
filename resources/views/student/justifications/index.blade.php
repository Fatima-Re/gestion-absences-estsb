@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mes justifications',
        'actions' => '<a href="' . route('student.justifications.create') . '" class="btn btn-primary">Nouvelle justification</a>'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetées</option>
                        <option value="needs_info" {{ request('status') == 'needs_info' ? 'selected' : '' }}>Informations requises</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="date_filter" class="form-label">Mois</label>
                    <input type="month" class="form-control" id="date_filter" name="month" value="{{ request('month') }}">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Date de l'absence</th>
                            <th>Raison</th>
                            <th>Date de soumission</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($justifications as $justification)
                        <tr>
                            <td>{{ $justification->absence->session->module->name ?? 'N/A' }}</td>
                            <td>{{ $justification->absence->session->date->format('d/m/Y') }}</td>
                            <td>{{ $justification->reason }}</td>
                            <td>{{ $justification->created_at->format('d/m/Y') }}</td>
                            <td>
                                @include('partials.status-badges', ['justificationStatus' => $justification->status])
                            </td>
                            <td>
                                <a href="{{ route('student.justifications.show', $justification) }}" class="btn btn-sm btn-info">Voir</a>
                                @if($justification->status === 'needs_info')
                                    <a href="{{ route('student.justifications.edit', $justification) }}" class="btn btn-sm btn-warning">Modifier</a>
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
document.getElementById('date_filter').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
