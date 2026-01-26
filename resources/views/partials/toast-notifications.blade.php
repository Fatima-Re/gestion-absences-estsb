{{-- Toast Notifications Container --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1060;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="successMessage">Action réalisée avec succès !</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="errorMessage">Une erreur s'est produite.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <div id="warningToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="warningMessage">Attention requise.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <div id="infoToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-info-circle me-2"></i>
                <span id="infoMessage">Information importante.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- JavaScript for Toast Notifications --}}
<script>
function showToast(type, message, duration = 5000) {
    const toastId = type + 'Toast';
    const messageId = type + 'Message';

    const toastElement = document.getElementById(toastId);
    const messageElement = document.getElementById(messageId);

    if (toastElement && messageElement) {
        messageElement.textContent = message;

        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        toast.show();
    }
}

// Success toast
function showSuccessToast(message, duration = 5000) {
    showToast('success', message, duration);
}

// Error toast
function showErrorToast(message, duration = 7000) {
    showToast('error', message, duration);
}

// Warning toast
function showWarningToast(message, duration = 6000) {
    showToast('warning', message, duration);
}

// Info toast
function showInfoToast(message, duration = 5000) {
    showToast('info', message, duration);
}

// Auto-show toasts from server flash messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showSuccessToast("{{ session('success') }}");
    @endif

    @if(session('error'))
        showErrorToast("{{ session('error') }}");
    @endif

    @if(session('warning'))
        showWarningToast("{{ session('warning') }}");
    @endif

    @if(session('info'))
        showInfoToast("{{ session('info') }}");
    @endif

    @if($errors->any())
        showErrorToast("{{ $errors->first() }}");
    @endif
});
</script>
