@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Génération de rapports',
        'subtitle' => 'Créer et exporter des rapports de présence détaillés',
        'actions' => '<button type="button" class="btn btn-success" onclick="exportAttendance()"><i class="fas fa-file-excel me-1"></i>Exporter présence</button>'
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nouveau rapport</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.reports.generate') }}" method="POST" id="reportForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="module_id" class="form-label">Module <span class="text-danger">*</span></label>
                                    <select class="form-select @error('module_id') is-invalid @enderror" id="module_id" name="module_id" required>
                                        <option value="">Sélectionner un module</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                                {{ $module->code }} - {{ $module->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('module_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="format" class="form-label">Format d'export <span class="text-danger">*</span></label>
                                    <select class="form-select @error('format') is-invalid @enderror" id="format" name="format" required>
                                        <option value="">Sélectionner un format</option>
                                        <option value="pdf" {{ old('format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                        <option value="excel" {{ old('format') == 'excel' ? 'selected' : '' }}>Excel</option>
                                    </select>
                                    @error('format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_from') is-invalid @enderror" id="date_from" name="date_from" value="{{ old('date_from', now()->subMonth()->format('Y-m-d')) }}" required>
                                    @error('date_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_to') is-invalid @enderror" id="date_to" name="date_to" value="{{ old('date_to', now()->format('Y-m-d')) }}" required>
                                    @error('date_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="group_id" class="form-label">Groupe (optionnel)</label>
                            <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="">Tous les groupes</option>
                                <!-- Groups will be populated based on selected module -->
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_comments" name="include_comments" value="1" {{ old('include_comments') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_comments">
                                            Inclure les commentaires
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_graphs" name="include_graphs" value="1" {{ old('include_graphs', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_graphs">
                                            Inclure les graphiques (PDF uniquement)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informations :</strong> Le rapport contiendra les statistiques de présence, la liste des étudiants absents, et les tendances d'assiduité pour la période sélectionnée.
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-export me-2"></i>Générer le rapport
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('teacher.modules.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Voir mes modules
                        </a>
                        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-users me-2"></i>Gérer les présences
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Types Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Types de rapports</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-file-pdf me-2"></i>PDF</h6>
                        <small class="text-muted">Rapport formaté avec graphiques et statistiques détaillées</small>
                    </div>

                    <div class="mb-0">
                        <h6 class="text-success"><i class="fas fa-file-excel me-2"></i>Excel</h6>
                        <small class="text-muted">Données brutes exportables pour analyse approfondie</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Rapports récents</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light">
                        <i class="fas fa-info-circle me-2"></i>
                        Les rapports générés seront automatiquement téléchargés. Vous pouvez les retrouver dans vos téléchargements.
                    </div>

                    <!-- Mock recent reports - in real app, this would come from database -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Période</th>
                                    <th>Format</th>
                                    <th>Généré le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>INFORMATIQUE-101</td>
                                    <td>01/12/2024 - 31/12/2024</td>
                                    <td><span class="badge bg-danger">PDF</span></td>
                                    <td>{{ now()->subDays(2)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MATHÉMATIQUES-201</td>
                                    <td>15/11/2024 - 15/12/2024</td>
                                    <td><span class="badge bg-success">Excel</span></td>
                                    <td>{{ now()->subDays(5)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dynamic group loading based on selected module
document.getElementById('module_id').addEventListener('change', function() {
    const moduleId = this.value;
    const groupSelect = document.getElementById('group_id');

    if (moduleId) {
        // In a real application, you would make an AJAX call to get groups for this module
        // For now, we'll show a placeholder
        groupSelect.innerHTML = '<option value="">Chargement...</option>';
    } else {
        groupSelect.innerHTML = '<option value="">Tous les groupes</option>';
    }
});

// Form validation
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;

    if (dateFrom && dateTo && dateFrom > dateTo) {
        e.preventDefault();
        alert('La date de début doit être antérieure à la date de fin.');
        return false;
    }
});

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(today.getMonth() - 1);

    document.getElementById('date_from').valueAsDate = oneMonthAgo;
    document.getElementById('date_to').valueAsDate = today;
});
</script>
@endsection
