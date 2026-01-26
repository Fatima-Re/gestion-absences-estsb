@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('partials.page-header', [
                'title' => 'Paramètres système',
                'subtitle' => 'Configuration générale de l\'application',
                'icon' => 'fas fa-cogs'
            ])

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                @foreach($groups as $groupKey => $groupName)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>{{ $groupName }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($settings[$groupKey]))
                                <div class="row">
                                    @foreach($settings[$groupKey] as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">
                                                {{ $setting->description }}
                                                @if(!$setting->is_editable)
                                                    <small class="text-muted">(Paramètre système)</small>
                                                @endif
                                            </label>

                                            @if($setting->type === \App\Models\Setting::TYPE_BOOLEAN)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                           value="1" {{ $setting->value ? 'checked' : '' }}
                                                           {{ !$setting->is_editable ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="{{ $setting->key }}">
                                                        Activé
                                                    </label>
                                                </div>
                                            @elseif($setting->type === \App\Models\Setting::TYPE_INTEGER)
                                                <input type="number" class="form-control"
                                                       id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                       value="{{ old($setting->key, $setting->value) }}"
                                                       {{ !$setting->is_editable ? 'disabled' : '' }}>
                                            @elseif($setting->options)
                                                <select class="form-select" id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                        {{ !$setting->is_editable ? 'disabled' : '' }}>
                                                    @foreach($setting->options as $optionValue => $optionLabel)
                                                        <option value="{{ $optionValue }}" {{ $setting->value == $optionValue ? 'selected' : '' }}>
                                                            {{ $optionLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control"
                                                       id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                       value="{{ $setting->value }}"
                                                       {{ !$setting->is_editable ? 'disabled' : '' }}>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">Aucun paramètre dans cette catégorie.</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('admin.settings.export') }}" class="btn btn-outline-info">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-header {
    background-color: var(--primary-color-1);
    color: white;
}

.form-check-label {
    font-weight: normal;
}

h6 {
    border-bottom: 2px solid var(--primary-color-2);
    padding-bottom: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add any JavaScript for dynamic settings if needed
    $('form').on('submit', function(e) {
        // Add loading state to submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...').prop('disabled', true);

        // Re-enable after 3 seconds (in case of slow response)
        setTimeout(() => {
            submitBtn.html(originalText).prop('disabled', false);
        }, 3000);
    });
});
</script>
@endpush
