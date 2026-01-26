{{-- Export Modal Component --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-export me-2"></i>Exporter les données
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="exportOptions">
                    {{-- Options will be dynamically loaded here --}}
                </div>

                {{-- Export progress indicator --}}
                <div id="exportProgress" class="d-none">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" style="width: 0%" id="exportProgressBar"></div>
                    </div>
                    <p class="text-center text-muted" id="exportStatus">Préparation de l'export...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" id="startExportBtn">
                    <i class="fas fa-download me-1"></i>Démarrer l'export
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Export Modal --}}
<script>
let currentExportType = '';
let currentExportUrl = '';
let currentExportFilters = {};

function openExportModal(type, title, optionsHtml = '', url = '', filters = {}) {
    document.getElementById('exportModalLabel').innerHTML = `<i class="fas fa-file-export me-2"></i>${title}`;

    const exportOptions = document.getElementById('exportOptions');
    const startExportBtn = document.getElementById('startExportBtn');

    if (optionsHtml) {
        exportOptions.innerHTML = optionsHtml;
        startExportBtn.style.display = 'inline-block';
    } else {
        exportOptions.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                L'export va commencer immédiatement avec les paramètres actuels.
            </div>
        `;
        startExportBtn.style.display = 'inline-block';
    }

    // Reset progress
    document.getElementById('exportProgress').classList.add('d-none');
    document.getElementById('exportProgressBar').style.width = '0%';

    currentExportType = type;
    currentExportUrl = url;
    currentExportFilters = filters;

    const modal = new bootstrap.Modal(document.getElementById('exportModal'));
    modal.show();
}

// Quick export functions
function exportStudents() {
    const optionsHtml = `
        <div class="mb-3">
            <h6>Options d'export des étudiants</h6>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeInactive" name="includeInactive">
                <label class="form-check-label" for="includeInactive">
                    Inclure les étudiants inactifs
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeGroups" name="includeGroups" checked>
                <label class="form-check-label" for="includeGroups">
                    Inclure les informations de groupe
                </label>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            L'export contiendra : Nom, Email, Numéro étudiant, Groupe, Année académique, etc.
        </div>
    `;

    openExportModal('students', 'Exporter les étudiants', optionsHtml, '{{ route("admin.import.export-students") }}');
}

function exportAbsences(filters = {}) {
    const optionsHtml = `
        <div class="mb-3">
            <h6>Filtres appliqués</h6>
            <p class="text-muted small">
                ${Object.keys(filters).length > 0 ?
                    'Filtres actifs : ' + Object.entries(filters).map(([k, v]) => `${k}: ${v}`).join(', ') :
                    'Aucun filtre appliqué - export de toutes les absences'}
            </p>
        </div>
        <div class="mb-3">
            <h6>Options d'export</h6>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeJustifications" name="includeJustifications" checked>
                <label class="form-check-label" for="includeJustifications">
                    Inclure le statut des justifications
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeSessionDetails" name="includeSessionDetails" checked>
                <label class="form-check-label" for="includeSessionDetails">
                    Inclure les détails de séance
                </label>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Format : Excel avec colonnes pour étudiant, module, date, statut, etc.
        </div>
    `;

    openExportModal('absences', 'Exporter les absences', optionsHtml, '{{ route("admin.absences.export") }}', filters);
}

function exportStatistics() {
    const optionsHtml = `
        <div class="mb-3">
            <h6>Période d'analyse</h6>
            <div class="row">
                <div class="col-md-6">
                    <label for="exportFromDate" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="exportFromDate" name="from_date"
                           value="${new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}">
                </div>
                <div class="col-md-6">
                    <label for="exportToDate" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="exportToDate" name="to_date"
                           value="${new Date().toISOString().split('T')[0]}">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <h6>Type de statistiques</h6>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statType" id="overallStats" value="overall" checked>
                <label class="form-check-label" for="overallStats">
                    Statistiques globales complètes
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statType" id="moduleStats" value="modules">
                <label class="form-check-label" for="moduleStats">
                    Statistiques par module
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statType" id="groupStats" value="groups">
                <label class="form-check-label" for="groupStats">
                    Statistiques par groupe
                </label>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-chart-bar me-2"></i>
            L'export contiendra plusieurs feuilles Excel avec tendances et analyses détaillées.
        </div>
    `;

    openExportModal('statistics', 'Exporter les statistiques', optionsHtml, '{{ route("admin.statistics.export") }}');
}

function exportAttendance() {
    // Get modules for current teacher (assuming we have access to teacher modules)
    const modules = @json(Auth::user()->teacher->modules ?? []);

    const optionsHtml = `
        <div class="mb-3">
            <h6>Sélection du module</h6>
            <select class="form-select" id="attendanceModule" name="module_id">
                <option value="">Tous les modules</option>
                ${modules.map(module => `<option value="${module.id}">${module.code} - ${module.name}</option>`).join('')}
            </select>
        </div>
        <div class="mb-3">
            <h6>Période</h6>
            <div class="row">
                <div class="col-md-6">
                    <label for="attendanceFromDate" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="attendanceFromDate" name="from_date"
                           value="${new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}">
                </div>
                <div class="col-md-6">
                    <label for="attendanceToDate" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="attendanceToDate" name="to_date"
                           value="${new Date().toISOString().split('T')[0]}">
                </div>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-user-check me-2"></i>
            Export détaillé avec feuilles séparées pour résumé, statistiques par groupe, etc.
        </div>
    `;

    openExportModal('attendance', 'Exporter la présence', optionsHtml, '{{ route("teacher.reports.export-attendance") }}');
}

// Handle export button click
document.getElementById('startExportBtn').addEventListener('click', function() {
    const exportProgress = document.getElementById('exportProgress');
    const exportProgressBar = document.getElementById('exportProgressBar');
    const exportStatus = document.getElementById('exportStatus');

    // Show progress
    exportProgress.classList.remove('d-none');
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Traitement...';

    // Simulate progress (in real implementation, this would be handled by server-sent events)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        exportProgressBar.style.width = progress + '%';

        const messages = [
            'Préparation des données...',
            'Application des filtres...',
            'Génération du fichier...',
            'Finalisation...'
        ];
        exportStatus.textContent = messages[Math.floor(progress / 25)] || 'Traitement...';
    }, 500);

    // Prepare form data
    const formData = new FormData();

    // Add specific options based on export type
    if (currentExportType === 'students') {
        formData.append('include_inactive', document.getElementById('includeInactive')?.checked || false);
        formData.append('include_groups', document.getElementById('includeGroups')?.checked || true);
    } else if (currentExportType === 'absences') {
        formData.append('include_justifications', document.getElementById('includeJustifications')?.checked || true);
        formData.append('include_session_details', document.getElementById('includeSessionDetails')?.checked || true);
        // Add current filters
        Object.entries(currentExportFilters).forEach(([key, value]) => {
            formData.append(key, value);
        });
    } else if (currentExportType === 'statistics') {
        formData.append('from_date', document.getElementById('exportFromDate')?.value || '');
        formData.append('to_date', document.getElementById('exportToDate')?.value || '');
        const statType = document.querySelector('input[name="statType"]:checked')?.value || 'overall';
        formData.append('type', statType);
    } else if (currentExportType === 'attendance') {
        formData.append('module_id', document.getElementById('attendanceModule')?.value || '');
        formData.append('from_date', document.getElementById('attendanceFromDate')?.value || '');
        formData.append('to_date', document.getElementById('attendanceToDate')?.value || '');
    }

    // Submit the export request
    fetch(currentExportUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors de l\'export');
        }
        return response.blob();
    })
    .then(blob => {
        // Complete progress
        clearInterval(progressInterval);
        exportProgressBar.style.width = '100%';
        exportStatus.textContent = 'Téléchargement...';

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `${currentExportType}_export_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);

        // Close modal after short delay
        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
            modal.hide();

            showSuccessToast('Export terminé avec succès !');

            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-download me-1"></i>Démarrer l\'export';
        }, 1000);
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Export error:', error);
        showErrorToast('Erreur lors de l\'export. Veuillez réessayer.');

        // Reset button
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-download me-1"></i>Démarrer l\'export';
    });
});
</script>
