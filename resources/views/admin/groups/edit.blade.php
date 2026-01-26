@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Modifier le groupe',
        'actions' => '<a href="' . route('admin.groups.show', $group) . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.groups.update', $group) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <label for="name" class="col-md-3 col-form-label">Nom</label>
                    <div class="col-md-9">
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $group->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="code" class="col-md-3 col-form-label">Code</label>
                    <div class="col-md-9">
                        <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $group->code) }}" required>
                        @error('code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="level" class="col-md-3 col-form-label">Niveau</label>
                    <div class="col-md-9">
                        <input id="level" type="text" class="form-control @error('level') is-invalid @enderror" name="level" value="{{ old('level', $group->level) }}" required>
                        @error('level')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="specialty" class="col-md-3 col-form-label">Spécialité</label>
                    <div class="col-md-9">
                        <input id="specialty" type="text" class="form-control @error('specialty') is-invalid @enderror" name="specialty" value="{{ old('specialty', $group->specialty) }}" required>
                        @error('specialty')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="max_students" class="col-md-3 col-form-label">Nombre max d'étudiants</label>
                    <div class="col-md-9">
                        <input id="max_students" type="number" class="form-control @error('max_students') is-invalid @enderror" name="max_students" value="{{ old('max_students', $group->max_students) }}" required min="1" max="100">
                        @error('max_students')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="academic_year" class="col-md-3 col-form-label">Année académique</label>
                    <div class="col-md-9">
                        <input id="academic_year" type="text" class="form-control @error('academic_year') is-invalid @enderror" name="academic_year" value="{{ old('academic_year', $group->academic_year) }}" required>
                        @error('academic_year')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="semester" class="col-md-3 col-form-label">Semestre</label>
                    <div class="col-md-9">
                        <select id="semester" class="form-select @error('semester') is-invalid @enderror" name="semester" required>
                            <option value="">Sélectionner un semestre</option>
                            <option value="1" {{ old('semester', $group->semester) == '1' ? 'selected' : '' }}>Semestre 1</option>
                            <option value="2" {{ old('semester', $group->semester) == '2' ? 'selected' : '' }}>Semestre 2</option>
                        </select>
                        @error('semester')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="is_active" class="col-md-3 col-form-label">Statut</label>
                    <div class="col-md-9">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $group->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Actif</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-9 offset-md-3">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
