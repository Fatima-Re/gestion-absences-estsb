@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mes absences'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="module_filter" class="form-label">Module</label>
                    <select class="form-select" id="module_filter" name="module_id">
                        <option value="">Tous les modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="justified" {{ request('status') == 'justified' ? 'selected' : '' }}>Justifiées</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="unjustified" {{ request('status') == 'unjustified' ? 'selected' : '' }}>Non justifiées</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date_filter" class="form-label">Mois</label>
                    <input type="month" class="form-control" id="date_filter" name="month" value="{{ request('month') }}">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absences as $absence)
                        <tr>
                            <td>{{ $absence->session->module->name ?? 'N/A' }}</td>
                            <td>{{ $absence->session->date->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($absence->session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($absence->session->end_time)->format('H:i') }}</td>
                            <td>
                                @include('partials.status-badges', ['status' => $absence->status])
                            </td>
                            <td>
                                @if($absence->status === 'pending' && !$absence->justification)
                                    <a href="{{ route('student.justifications.create', $absence) }}" class="btn btn-sm btn-primary">Justifier</a>
                                @elseif($absence->justification)
                                    <span class="text-muted">Justification soumise</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune absence trouvée.</td>
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
document.getElementById('status_filter').addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('date_filter').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
