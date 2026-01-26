@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Détails de l\'absence',
        'actions' => '<a href="' . route('admin.absences.index') . '" class="btn btn-secondary me-2">Retour</a>' .
                     ($absence->status === 'pending' ?
                         '<form action="' . route('admin.absences.update', $absence) . '" method="POST" class="d-inline me-2">
                             @csrf
                             @method("PATCH")
                             <input type="hidden" name="status" value="justified">
                             <button type="submit" class="btn btn-success">Marquer comme justifiée</button>
                         </form>
                         <form action="' . route('admin.absences.update', $absence) . '" method="POST" class="d-inline">
                             @csrf
                             @method("PATCH")
                             <input type="hidden" name="status" value="unjustified">
                             <button type="submit" class="btn btn-danger">Marquer comme non justifiée</button>
                         </form>' : '')
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'absence</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Étudiant</h6>
                            <p class="mb-3">{{ $absence->student->user->name }}</p>

                            <h6>Email</h6>
                            <p class="mb-3">{{ $absence->student->user->email }}</p>

                            <h6>Groupe</h6>
                            <p class="mb-3">{{ $absence->student->group->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Module</h6>
                            <p class="mb-3">{{ $absence->session->module->name ?? 'N/A' }}</p>

                            <h6>Date de la session</h6>
                            <p class="mb-3">{{ $absence->session->date->format('d/m/Y') }}</p>

                            <h6>Heure de la session</h6>
                            <p class="mb-3">{{ \Carbon\Carbon::parse($absence->session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($absence->session->end_time)->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Statut</h6>
                            <p class="mb-3">
                                @include('partials.status-badges', ['status' => $absence->status])
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Enseignant</h6>
                            <p class="mb-3">{{ $absence->session->module->teacher->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($absence->justification)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Justification</h5>
                </div>
                <div class="card-body">
                    <h6>Raison</h6>
                    <p class="mb-3">{{ $absence->justification->reason }}</p>

                    <h6>Description</h6>
                    <p class="mb-3">{{ $absence->justification->description ?? 'Aucune description' }}</p>

                    <h6>Document</h6>
                    @if($absence->justification->document_path)
                        <p><a href="{{ asset('storage/' . $absence->justification->document_path) }}" target="_blank" class="btn btn-sm btn-primary">Voir le document</a></p>
                    @else
                        <p class="text-muted">Aucun document fourni</p>
                    @endif

                    <h6>Date de soumission</h6>
                    <p class="mb-3">{{ $absence->justification->created_at->format('d/m/Y H:i') }}</p>

                    <h6>Statut de la justification</h6>
                    <p>
                        @include('partials.status-badges', ['justificationStatus' => $absence->justification->status])
                    </p>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Aucune justification soumise pour cette absence.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
