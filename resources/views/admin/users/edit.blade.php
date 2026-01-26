@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Modifier l\'utilisateur',
        'actions' => '<a href="' . route('admin.users.show', $user) . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <label for="name" class="col-md-3 col-form-label">Nom complet</label>
                    <div class="col-md-9">
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="email" class="col-md-3 col-form-label">Email</label>
                    <div class="col-md-9">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="phone" class="col-md-3 col-form-label">Téléphone</label>
                    <div class="col-md-9">
                        <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                @if($user->role === 'student' && $user->student)
                <div class="row mb-3">
                    <label for="student_number" class="col-md-3 col-form-label">Numéro étudiant</label>
                    <div class="col-md-9">
                        <input id="student_number" type="text" class="form-control @error('student_number') is-invalid @enderror" name="student_number" value="{{ old('student_number', $user->student->student_number) }}" required>
                        @error('student_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="group_id" class="col-md-3 col-form-label">Groupe</label>
                    <div class="col-md-9">
                        <select id="group_id" class="form-select @error('group_id') is-invalid @enderror" name="group_id" required>
                            <option value="">Sélectionner un groupe</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id', $user->student->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                @endif

                @if($user->role === 'teacher' && $user->teacher)
                <div class="row mb-3">
                    <label for="teacher_code" class="col-md-3 col-form-label">Code enseignant</label>
                    <div class="col-md-9">
                        <input id="teacher_code" type="text" class="form-control @error('teacher_code') is-invalid @enderror" name="teacher_code" value="{{ old('teacher_code', $user->teacher->teacher_code) }}" required>
                        @error('teacher_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="specialization" class="col-md-3 col-form-label">Spécialisation</label>
                    <div class="col-md-9">
                        <input id="specialization" type="text" class="form-control @error('specialization') is-invalid @enderror" name="specialization" value="{{ old('specialization', $user->teacher->specialization) }}">
                        @error('specialization')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                @endif

                <div class="row mb-0">
                    <div class="col-md-9 offset-md-3">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
