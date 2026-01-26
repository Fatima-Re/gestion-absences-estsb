@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mes modules',
        'subtitle' => 'Gestion des modules que vous enseignez'
    ])

    <div class="row">
        @forelse($modules as $module)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-book me-2 text-primary"></i>
                            {{ $module->code }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $module->name }}</h5>

                        <div class="mb-2">
                            <small class="text-muted">Crédits:</small>
                            <span class="badge bg-info">{{ $module->credits }}</span>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Semestre:</small>
                            <span class="badge bg-secondary">{{ $module->semester }}</span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Groupes:</small>
                            <div class="mt-1">
                                @forelse($module->groups as $group)
                                    <span class="badge bg-light text-dark me-1">{{ $group->name }}</span>
                                @empty
                                    <span class="text-muted">Aucun groupe</span>
                                @endforelse
                            </div>
                        </div>

                        @if($module->description)
                            <p class="card-text text-muted small">{{ Str::limit($module->description, 100) }}</p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('teacher.modules.show', $module) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Voir détails
                            </a>
                            <a href="{{ route('teacher.modules.statistics', $module) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar me-1"></i>Statistiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun module assigné</h5>
                        <p class="text-muted">Vous n'avez actuellement aucun module à enseigner.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($modules->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $modules->links() }}
        </div>
    @endif
</div>
@endsection
