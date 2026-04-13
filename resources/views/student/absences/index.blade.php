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
            <form method="GET" action="{{ route('student.absences.index') }}" id="absence-filter-form">
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
            </form>

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
                                @if($absence->isJustifiable() && !$absence->justification)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary btn-justify-absence"
                                        data-bs-toggle="modal"
                                        data-bs-target="#justifyAbsenceModal"
                                        data-absence-id="{{ $absence->id }}"
                                        data-module="{{ $absence->session->module->name ?? 'N/A' }}"
                                        data-date="{{ $absence->session->date->format('Y-m-d') }}"
                                    >
                                        Justifier
                                    </button>
                                @elseif($absence->justification)
                                    <a href="{{ route('student.justifications.show', $absence->justification) }}" class="btn btn-sm btn-outline-secondary">
                                        Voir la justification
                                    </a>
                                @else
                                    <span class="text-muted">Délai expiré</span>
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

<!-- Justification Modal -->
<div class="modal fade" id="justifyAbsenceModal" tabindex="-1" aria-labelledby="justifyAbsenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('student.justifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="absence_id" id="modal_absence_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="justifyAbsenceModalLabel">Soumettre une justification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong id="modal_module"></strong><br>
                        Date: <span id="modal_date_display"></span>
                    </div>

                    <div class="mb-3">
                        <label for="modal_type" class="form-label">Type de justification <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_type" name="type" required>
                            <option value="">Sélectionner un type</option>
                            @foreach($justificationTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_start_date" class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="modal_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_end_date" class="form-label">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="modal_end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_description" class="form-label">Description</label>
                        <textarea class="form-control" id="modal_description" name="description" rows="3" maxlength="500"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="modal_file" class="form-label">Document justificatif <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="modal_file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Formats: PDF, JPG, JPEG, PNG. Taille max: 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Soumettre la justification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const filterForm = document.getElementById('absence-filter-form');
document.getElementById('module_filter').addEventListener('change', function() {
    filterForm.submit();
});
document.getElementById('status_filter').addEventListener('change', function() {
    filterForm.submit();
});
document.getElementById('date_filter').addEventListener('change', function() {
    filterForm.submit();
});

document.querySelectorAll('.btn-justify-absence').forEach((btn) => {
    btn.addEventListener('click', function () {
        const absenceId = this.dataset.absenceId;
        const moduleName = this.dataset.module;
        const dateValue = this.dataset.date;

        document.getElementById('modal_absence_id').value = absenceId;
        document.getElementById('modal_module').textContent = moduleName;
        document.getElementById('modal_date_display').textContent = dateValue;
        document.getElementById('modal_start_date').value = dateValue;
        document.getElementById('modal_end_date').value = dateValue;
    });
});
</script>
@endsection
