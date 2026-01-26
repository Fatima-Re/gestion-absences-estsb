@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Préférences de notification',
        'actions' => '<a href="' . route('student.notifications.index') . '" class="btn btn-secondary">Retour aux notifications</a>'
    ])

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Paramètres des notifications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Configurez vos préférences pour recevoir des notifications sur les événements importants.
                    </p>

                    <form action="{{ route('student.notifications.update-preferences') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Types de notifications</h6>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="absence_alerts" name="absence_alerts" value="1" {{ $preferences['absence_alerts'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="absence_alerts">
                                        <strong>Alertes d'absence</strong>
                                        <br><small class="text-muted">Notifications lorsque vous êtes marqué absent</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="justification_updates" name="justification_updates" value="1" {{ $preferences['justification_updates'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="justification_updates">
                                        <strong>Mises à jour des justifications</strong>
                                        <br><small class="text-muted">Statut de vos demandes de justification</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="session_cancellations" name="session_cancellations" value="1" {{ $preferences['session_cancellations'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="session_cancellations">
                                        <strong>Annulations de séances</strong>
                                        <br><small class="text-muted">Alertes sur les changements d'emploi du temps</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="mb-3">Méthodes de notification</h6>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ $preferences['email_notifications'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        <strong>Notifications par email</strong>
                                        <br><small class="text-muted">Recevoir les notifications par email</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="push_notifications" name="push_notifications" value="1" {{ $preferences['push_notifications'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="push_notifications">
                                        <strong>Notifications push</strong>
                                        <br><small class="text-muted">Notifications dans l'application</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Note importante</h6>
                            <p class="mb-0">
                                Certaines notifications critiques (comme les alertes d'absence) peuvent toujours être envoyées
                                même si vous désactivez les notifications générales, pour des raisons pédagogiques.
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les préférences
                                </button>
                                <a href="{{ route('student.notifications.index') }}" class="btn btn-secondary ms-3">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notification Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques des notifications</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-primary text-white rounded">
                                <h4 class="mb-1">{{ \App\Models\Notification::where('user_id', auth()->id())->count() }}</h4>
                                <small>Total des notifications</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-warning text-white rounded">
                                <h4 class="mb-1">{{ \App\Models\Notification::where('user_id', auth()->id())->unread()->count() }}</h4>
                                <small>Non lues</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-success text-white rounded">
                                <h4 class="mb-1">{{ \App\Models\Notification::where('user_id', auth()->id())->read()->count() }}</h4>
                                <small>Lues</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-info text-white rounded">
                                <h4 class="mb-1">{{ \App\Models\Notification::where('user_id', auth()->id())->where('type', 'warning')->count() }}</h4>
                                <small>Avertissements</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
