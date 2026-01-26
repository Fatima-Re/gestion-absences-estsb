@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails de la justification',
        'actions' => '<a href="' . route('admin.justifications.index') . '" class="btn btn-secondary me-2">Retour</a>' .
                     ($justification->status === 'pending' ?
                         '<form action="' . route('admin.justifications.update', $justification) . '" method="POST" class="d-inline me-2">
                             @csrf
                             @method("PATCH")
                             <input type="hidden" name="status" value="approved">
                             <button type="submit" class="btn btn-success">Approuver</button>
                         </form>
                         <form action="' . route('admin.justifications.update', $justification) . '" method="POST" class="d-inline me-2">
                             @csrf
                             @method("PATCH")
                             <input type="hidden" name="status" value="rejected">
                             <button type="submit" class="btn btn-danger">Rejeter</button>
                         </form>
                         <form action="' . route('admin.justifications.update', $justification) . '" method="POST" class="d-inline">
                             @csrf
                             @method("PATCH")
                             <input type="hidden" name="status" value="needs_info">
                             <button type="submit" class="btn btn-warning">Demander info</button>
                         </form>' : '')
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
                            <h6>Étudiant</h6>
                            <p class="mb-3">{{ $justification->absence->student->user->name }}</p>

                            <h6>Email</h6>
                            <p class="mb-3">{{ $justification->absence->student->user->email }}</p>

                            <h6>Groupe</h6>
                            <p class="mb-3">{{ $justification->absence->student->group->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Module</h6>
                            <p class="mb-3">{{ $justification->absence->session->module->name ?? 'N/A' }}</p>

                            <h6>Date de l'absence</h6>
                            <p class="mb-3">{{ $justification->absence->session->date->format('d/m/Y') }}</p>

                            <h6>Heure de l'absence</h6>
                            <p class="mb-3">{{ \Carbon\Carbon::parse($justification->absence->session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($justification->absence->session->end_time)->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Raison</h6>
                            <p class="mb-3">{{ $justification->reason }}</p>

                            <h6>Description</h6>
                            <p class="mb-3">{{ $justification->description ?? 'Aucune description' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Date de soumission</h6>
                            <p class="mb-3">{{ $justification->created_at->format('d/m/Y H:i') }}</p>

                            <h6>Statut</h6>
                            <p class="mb-3">
                                @include('partials.status-badges', ['justificationStatus' => $justification->status])
                            </p>

                            <h6>Dernière mise à jour</h6>
                            <p class="mb-3">{{ $justification->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Document justificatif</h5>
                </div>
                <div class="card-body">
                    @if($justification->document_path)
                        <div class="text-center">
                            @if(strtolower(pathinfo($justification->document_path, PATHINFO_EXTENSION)) === 'pdf')
                                <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                            @elseif(in_array(strtolower(pathinfo($justification->document_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/' . $justification->document_path) }}" alt="Justificatif" class="img-fluid mb-3" style="max-height: 200px;">
                            @else
                                <i class="fas fa-file fa-3x text-secondary mb-3"></i>
                            @endif
                            <br>
                            <a href="{{ asset('storage/' . $justification->document_path) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Voir le document
                            </a>
                            <br>
                            <small class="text-muted">{{ basename($justification->document_path) }}</small>
                        </div>
                    @else
                        <p class="text-muted text-center">Aucun document fourni</p>
                    @endif
                </div>
            </div>

            @if($justification->admin_comment)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Commentaire administrateur</h5>
                </div>
                <div class="card-body">
                    <p>{{ $justification->admin_comment }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($justification->status === 'needs_info')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-white">Informations supplémentaires requises</h5>
                </div>
                <div class="card-body">
                    <p>Des informations supplémentaires ont été demandées à l'étudiant concernant cette justification.</p>
                    @if($justification->admin_comment)
                        <p><strong>Message envoyé :</strong> {{ $justification->admin_comment }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
