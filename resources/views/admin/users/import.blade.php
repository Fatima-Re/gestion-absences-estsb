@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('partials.page-header', [
                'title' => 'Importation des utilisateurs',
                'subtitle' => 'Importer des étudiants en masse depuis un fichier Excel/CSV',
                'icon' => 'fas fa-upload'
            ])

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Import Form -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-upload me-2"></i>Importer des étudiants
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.import.students') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="file" class="form-label">
                                        Fichier Excel/CSV <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="file" name="file"
                                           accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text">
                                        Formats acceptés: .xlsx, .xls, .csv
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="group_id" class="form-label">
                                        Groupe par défaut <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="group_id" name="group_id" required>
                                        <option value="">Sélectionner un groupe</option>
                                        @foreach(\App\Models\Group::active()->get() as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        Tous les étudiants importés seront assignés à ce groupe par défaut
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="send_welcome_emails" name="send_welcome_emails" value="1">
                                        <label class="form-check-label" for="send_welcome_emails">
                                            Envoyer les emails de bienvenue avec les identifiants
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Importer les étudiants
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Instructions
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Format du fichier</h6>
                            <p class="text-muted small mb-3">
                                Votre fichier doit contenir les colonnes suivantes (dans cet ordre) :
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Colonne</th>
                                            <th>Description</th>
                                            <th>Obligatoire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>nom</code></td>
                                            <td>Nom complet de l'étudiant</td>
                                            <td><span class="badge bg-danger">Oui</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>email</code></td>
                                            <td>Adresse email</td>
                                            <td><span class="badge bg-danger">Oui</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>numero_etudiant</code></td>
                                            <td>Numéro d'étudiant unique</td>
                                            <td><span class="badge bg-danger">Oui</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>telephone</code></td>
                                            <td>Numéro de téléphone</td>
                                            <td><span class="badge bg-warning">Non</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Astuce :</strong> Téléchargez d'abord la liste des étudiants existants pour voir le format exact.
                            </div>
                        </div>
                    </div>

                    <!-- Export existing students -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-download me-2"></i>Exporter les données
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Téléchargez la liste des étudiants existants pour référence.
                            </p>

                            <form action="{{ route('admin.import.export-students') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_inactive" name="include_inactive" value="1">
                                        <label class="form-check-label" for="include_inactive">
                                            Inclure les étudiants inactifs
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-download me-2"></i>Télécharger Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-header {
    background-color: var(--primary-color-1);
    color: white;
}

.table code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875em;
}

.alert-info {
    border-color: var(--primary-color-2);
    background-color: rgba(7, 122, 162, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // File validation
    $('#file').on('change', function() {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                 'application/vnd.ms-excel',
                                 'text/csv'];

            if (!allowedTypes.includes(file.type)) {
                alert('Veuillez sélectionner un fichier Excel (.xlsx, .xls) ou CSV.');
                this.value = '';
            }
        }
    });

    // Form submission
    $('form').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Importation en cours...')
                .prop('disabled', true);

        // Re-enable after 10 seconds (in case of slow response)
        setTimeout(() => {
            submitBtn.html(originalText).prop('disabled', false);
        }, 10000);
    });
});
</script>
@endpush
