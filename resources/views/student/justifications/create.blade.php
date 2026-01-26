@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Soumettre une justification',
        'actions' => '<a href="' . route('student.justifications.index') . '" class="btn btn-secondary">Retour à mes justifications</a>'
    ])

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Justification d'absence</h5>
                </div>
                <div class="card-body">
                    <!-- Absence Information -->
                    <div class="alert alert-info">
                        <h6>Informations sur l'absence</h6>
                        <p class="mb-1"><strong>Module:</strong> {{ $absence->session->module->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ $absence->session->date->format('d/m/Y') }}</p>
                        <p class="mb-1"><strong>Heure:</strong> {{ \Carbon\Carbon::parse($absence->session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($absence->session->end_time)->format('H:i') }}</p>
                        <p class="mb-0"><strong>Statut actuel:</strong> @include('partials.status-badges', ['status' => $absence->status])</p>
                    </div>

                    <form action="{{ route('student.justifications.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="absence_id" value="{{ $absence->id }}">

                        <div class="mb-3">
                            <label for="type" class="form-label">Type de justification <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Sélectionner un type</option>
                                @foreach($justificationTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Choisissez le type de justification approprié. Un certificat médical est requis pour les absences médicales.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $absence->session->date->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $absence->session->date->format('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description détaillée</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Expliquez brièvement les raisons de votre absence...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Fournissez autant de détails que possible pour aider à l'évaluation de votre justification.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Document justificatif <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Formats acceptés: PDF, JPG, JPEG, PNG. Taille maximale: 5MB.
                                @if(old('type') === 'medical')
                                    Pour les certificats médicaux, assurez-vous que le document est lisible et contient toutes les informations nécessaires.
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                            <ul class="mb-0">
                                <li>La justification doit être soumise dans les 7 jours suivant l'absence.</li>
                                <li>Tous les champs marqués d'un * sont obligatoires.</li>
                                <li>Assurez-vous que le document est lisible et complet.</li>
                                <li>Une fois soumise, votre justification sera examinée par l'administration.</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Soumettre la justification
                                </button>
                                <a href="{{ route('student.absences.index') }}" class="btn btn-secondary ms-2">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Date validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('end_date');
    const endDate = new Date(endDateInput.value);

    if (endDate < startDate) {
        endDateInput.value = this.value;
    }
    endDateInput.min = this.value;
});

document.getElementById('end_date').addEventListener('change', function() {
    const endDate = new Date(this.value);
    const startDateInput = document.getElementById('start_date');
    const startDate = new Date(startDateInput.value);

    if (endDate < startDate) {
        startDateInput.value = this.value;
    }
});
</script>
@endsection
