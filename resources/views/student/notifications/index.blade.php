@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mes notifications',
        'actions' => '<button type="button" class="btn btn-success me-2" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Tout marquer comme lu
                      </button>
                      <button type="button" class="btn btn-outline-secondary" onclick="clearRead()">
                        <i class="fas fa-trash"></i> Supprimer les lues
                      </button>'
    ])

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="status_filter" class="form-label">Statut</label>
                    <select class="form-select" id="status_filter" name="status">
                        <option value="">Toutes</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Non lues</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lues</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="type_filter" class="form-label">Type</label>
                    <select class="form-select" id="type_filter" name="type">
                        <option value="">Tous les types</option>
                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Informations</option>
                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Avertissements</option>
                        <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Succès</option>
                        <option value="danger" {{ request('type') == 'danger' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Actions rapides</label><br>
                    <a href="{{ route('student.notifications.preferences') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cog"></i> Préférences
                    </a>
                </div>
            </div>

            <div class="list-group">
                @forelse($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}">
                    <div class="d-flex w-100 justify-content-between">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0 me-3">{{ $notification->title }}</h6>
                                @if($notification->priority === 'high')
                                    <span class="badge bg-danger">Priorité haute</span>
                                @elseif($notification->priority === 'normal')
                                    <span class="badge bg-warning">Priorité normale</span>
                                @endif
                                @if(!$notification->read_at)
                                    <span class="badge bg-primary ms-2">Nouveau</span>
                                @endif
                            </div>

                            <p class="mb-2">{{ $notification->message }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                    @if($notification->related_model && $notification->related_id)
                                        • Lié à {{ $notification->related_model }}
                                    @endif
                                </small>

                                <div class="btn-group btn-group-sm">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('student.notifications.read', $notification) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-check"></i> Marquer comme lu
                                            </button>
                                        </form>
                                    @endif

                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-external-link-alt"></i> Voir
                                        </a>
                                    @endif

                                    <form action="{{ route('student.notifications.destroy', $notification) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-3"></i>
                    <p class="mb-0">Aucune notification trouvée.</p>
                </div>
                @endforelse
            </div>

            {{ $notifications->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
// Filter functionality
document.getElementById('status_filter').addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.value) {
        url.searchParams.set('status', this.value);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
});

document.getElementById('type_filter').addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.value) {
        url.searchParams.set('type', this.value);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
});

// Mark all as read
function markAllAsRead() {
    if (confirm('Marquer toutes les notifications comme lues?')) {
        fetch('{{ route("student.notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(() => {
            window.location.reload();
        });
    }
}

// Clear read notifications
function clearRead() {
    if (confirm('Supprimer toutes les notifications lues?')) {
        // This would need a route for clearing read notifications
        // For now, we'll just reload
        window.location.reload();
    }
}
</script>
@endsection
