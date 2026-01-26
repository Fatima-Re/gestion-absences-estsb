{{-- Confirmation Modal for Delete Actions --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirmation requise
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Cette action ne peut pas être annulée.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmButton">
                    <i class="fas fa-trash me-1"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Confirmation Modal --}}
<script>
function showConfirmationModal(message, confirmCallback, confirmButtonText = 'Confirmer', confirmButtonClass = 'btn-danger') {
    document.getElementById('confirmationMessage').textContent = message;

    const confirmButton = document.getElementById('confirmButton');
    confirmButton.innerHTML = `<i class="fas fa-check me-1"></i>${confirmButtonText}`;
    confirmButton.className = `btn ${confirmButtonClass}`;

    // Remove previous event listeners
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

    // Add new event listener
    newConfirmButton.addEventListener('click', function() {
        confirmCallback();
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
        modal.hide();
    });

    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    modal.show();
}

// Helper function for delete confirmations
function confirmDelete(url, message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    showConfirmationModal(message, function() {
        // Create and submit a form to the URL
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }

        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }, 'Supprimer', 'btn-danger');
}
</script>
