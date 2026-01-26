{{-- Enhanced Status Badges with Icons and Better Styling --}}

{{-- Status Badge for Absence --}}
@if(isset($status))
    @if($status === 'justified' || $status === \App\Models\Absence::STATUS_JUSTIFIED)
        <span class="badge bg-success rounded-pill px-2 py-1">
            <i class="fas fa-check-circle me-1"></i>Justifiée
        </span>
    @elseif($status === 'pending' || $status === \App\Models\Absence::STATUS_PENDING)
        <span class="badge bg-warning rounded-pill px-2 py-1">
            <i class="fas fa-clock me-1"></i>En attente
        </span>
    @elseif($status === 'unjustified' || $status === \App\Models\Absence::STATUS_UNJUSTIFIED)
        <span class="badge bg-danger rounded-pill px-2 py-1">
            <i class="fas fa-times-circle me-1"></i>Non justifiée
        </span>
    @else
        <span class="badge bg-secondary rounded-pill px-2 py-1">
            <i class="fas fa-question-circle me-1"></i>{{ $status }}
        </span>
    @endif
@endif

{{-- Status Badge for Justification --}}
@if(isset($justificationStatus))
    @if($justificationStatus === 'approved' || $justificationStatus === \App\Models\Justification::STATUS_APPROVED)
        <span class="badge bg-success rounded-pill px-2 py-1">
            <i class="fas fa-thumbs-up me-1"></i>Approuvée
        </span>
    @elseif($justificationStatus === 'pending' || $justificationStatus === \App\Models\Justification::STATUS_PENDING)
        <span class="badge bg-warning rounded-pill px-2 py-1">
            <i class="fas fa-clock me-1"></i>En attente
        </span>
    @elseif($justificationStatus === 'rejected' || $justificationStatus === \App\Models\Justification::STATUS_REJECTED)
        <span class="badge bg-danger rounded-pill px-2 py-1">
            <i class="fas fa-thumbs-down me-1"></i>Rejetée
        </span>
    @elseif($justificationStatus === 'needs_info' || $justificationStatus === \App\Models\Justification::STATUS_NEEDS_INFO)
        <span class="badge bg-info rounded-pill px-2 py-1">
            <i class="fas fa-info-circle me-1"></i>Informations requises
        </span>
    @else
        <span class="badge bg-secondary rounded-pill px-2 py-1">
            <i class="fas fa-question-circle me-1"></i>{{ $justificationStatus }}
        </span>
    @endif
@endif

{{-- Status Badge for Session --}}
@if(isset($sessionStatus))
    @if($sessionStatus === 'scheduled' || $sessionStatus === \App\Models\CourseSession::STATUS_SCHEDULED)
        <span class="badge bg-primary rounded-pill px-2 py-1">
            <i class="fas fa-calendar-check me-1"></i>Planifiée
        </span>
    @elseif($sessionStatus === 'completed' || $sessionStatus === \App\Models\CourseSession::STATUS_COMPLETED)
        <span class="badge bg-success rounded-pill px-2 py-1">
            <i class="fas fa-check-double me-1"></i>Terminée
        </span>
    @elseif($sessionStatus === 'cancelled' || $sessionStatus === \App\Models\CourseSession::STATUS_CANCELLED)
        <span class="badge bg-danger rounded-pill px-2 py-1">
            <i class="fas fa-ban me-1"></i>Annulée
        </span>
    @else
        <span class="badge bg-secondary rounded-pill px-2 py-1">
            <i class="fas fa-question-circle me-1"></i>{{ $sessionStatus }}
        </span>
    @endif
@endif

{{-- Active/Inactive Badge --}}
@if(isset($isActive))
    @if($isActive)
        <span class="badge bg-success rounded-pill px-2 py-1">
            <i class="fas fa-circle me-1"></i>Actif
        </span>
    @else
        <span class="badge bg-secondary rounded-pill px-2 py-1">
            <i class="fas fa-circle me-1"></i>Inactif
        </span>
    @endif
@endif

{{-- Role Badge --}}
@if(isset($role))
    @if($role === 'admin')
        <span class="badge bg-danger rounded-pill px-2 py-1">
            <i class="fas fa-crown me-1"></i>Administrateur
        </span>
    @elseif($role === 'teacher')
        <span class="badge bg-primary rounded-pill px-2 py-1">
            <i class="fas fa-chalkboard-teacher me-1"></i>Enseignant
        </span>
    @elseif($role === 'student')
        <span class="badge bg-success rounded-pill px-2 py-1">
            <i class="fas fa-user-graduate me-1"></i>Étudiant
        </span>
    @else
        <span class="badge bg-secondary rounded-pill px-2 py-1">
            <i class="fas fa-user me-1"></i>{{ $role }}
        </span>
    @endif
@endif
