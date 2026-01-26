@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails de la justification',
        'actions' => '<a href="' . route('student.justifications.index') . '" class="btn btn-secondary me-2">Retour</a>' .
                     ($justification->status === \App\Models\Justification::STATUS_NEEDS_INFO ?
                         '<a href="' . route('student.justifications.edit', $justification) . '" class="btn btn-warning">Modifier</a>' : '')
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la justification</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Module</h6>
                            <p class="mb-3">{{ $justification->absence->session->module->name ?? 'N/A' }}</p>

                            <h6>Date de l'absence</h6>
                            <p class="mb-3">{{ $justification->absence->session->date->format('d/m/Y') }}</p>

                            <h6>Type de justification</h6>
                            <p class="mb-3">
                                @switch($justification->type)
                                    @case(\App\Models\Justification::TYPE_MEDICAL)
                                        Certificat médical
                                        @break
                                    @case(\App\Models\Justification::TYPE_OFFICIAL)
                                        Convocation officielle
                                        @break
                                    @case(\App\Models\Justification::TYPE_PERSONAL)
                                        Raison personnelle
                                        @break
                                    @case(\App\Models\Justification::TYPE_TRANSPORT)
                                        Problème de transport
                                        @break
                                    @default
                                        Autre
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Période</h6>
                            <p class="mb-3">
                                Du {{ $justification->start_date->format('d/m/Y') }}
                                au {{ $justification->end_date->format('d/m/Y') }}
                            </p>

                            <h6>Statut</h6>
                            <p class="mb-3">
                                @include('partials.status-badges', ['justificationStatus' => $justification->status])
                            </p>

                            <h6>Date de soumission</h6>
                            <p class="mb-3">{{ $justification->submitted_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($justification->description)
                    <div class="row">
                        <div class="col-12">
                            <h6>Description</h6>
                            <p>{{ $justification->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($justification->admin_comment)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-comment"></i> Commentaire de l'administrateur</h6>
                                <p class="mb-0">{{ $justification->admin_comment }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Document justificatif</h5>
                </div>
                <div class="card-body">
                    @if($justification->file_path)
                        <div class="text-center">
                            @if(strtolower(pathinfo($justification->file_name, PATHINFO_EXTENSION)) === 'pdf')
                                <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                            @elseif(in_array(strtolower(pathinfo($justification->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/' . $justification->file_path) }}" alt="Justificatif" class="img-fluid mb-3" style="max-height: 200px;">
                            @else
                                <i class="fas fa-file fa-3x text-secondary mb-3"></i>
                            @endif
                            <br>
                            <a href="{{ asset('storage/' . $justification->file_path) }}" target="_blank" class="btn btn-primary mb-2">
                                <i class="fas fa-eye"></i> Voir le document
                            </a>
                            <br>
                            <small class="text-muted">
                                {{ $justification->file_name }}<br>
                                Taille: {{ number_format($justification->file_size / 1024, 1) }} KB
                            </small>
                        </div>
                    @else
                        <p class="text-muted text-center">Aucun document fourni</p>
                    @endif
                </div>
            </div>

            @if($justification->status === \App\Models\Justification::STATUS_NEEDS_INFO)
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-exclamation-triangle"></i> Informations supplémentaires requises
                    </h5>
                </div>
                <div class="card-body">
                    <p>Votre justification nécessite des informations supplémentaires. Veuillez la modifier en conséquence.</p>
                    <a href="{{ route('student.justifications.edit', $justification) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Modifier la justification
                    </a>
                </div>
            </div>
            @endif

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Historique</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Soumission</h6>
                                <p class="timeline-text">{{ $justification->submitted_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($justification->reviewed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Révision</h6>
                                <p class="timeline-text">{{ $justification->reviewed_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 4px;
}

.timeline-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endsection
