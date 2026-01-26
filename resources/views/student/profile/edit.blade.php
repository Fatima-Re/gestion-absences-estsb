@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Modifier mon profil',
        'actions' => '<a href="' . route('student.profile.index') . '" class="btn btn-secondary">Retour au profil</a>'
    ])

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                                <i class="fas fa-user me-2"></i>Informations personnelles
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                                <i class="fas fa-address-book me-2"></i>Contact
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                                <i class="fas fa-shield-alt me-2"></i>Sécurité
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                                <i class="fas fa-cogs me-2"></i>Préférences
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="section" value="personal">

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">Date de naissance</label>
                                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->subYears(15)->format('Y-m-d') }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="photo" class="form-label">Photo de profil</label>
                                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                                            <div class="form-text">Formats acceptés: JPEG, PNG, JPG. Taille maximale: 2MB</div>
                                            @error('photo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @if($student->photo_url)
                                                <div class="mt-2">
                                                    <small class="text-muted">Photo actuelle:</small><br>
                                                    <img src="{{ asset('storage/' . $student->photo_url) }}" alt="Photo actuelle" class="mt-1 rounded" style="max-width: 100px; max-height: 100px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4 text-center">
                                        <div class="mb-3">
                                            <label class="form-label">Aperçu</label>
                                            <div id="photo-preview">
                                                @if($student->photo_url)
                                                    <img src="{{ asset('storage/' . $student->photo_url) }}" alt="Photo de profil" class="rounded-circle" width="120" height="120">
                                                @else
                                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($student->photo_url)
                                            <div class="mb-3">
                                                <form action="{{ route('student.profile.deletePhoto') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash me-2"></i>Supprimer la photo
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Contact Information Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            <form action="{{ route('student.profile.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="contact">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Ex: +212 6XX XXX XXX">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergency_contact_name" class="form-label">Contact d'urgence - Nom</label>
                                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}">
                                            @error('emergency_contact_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergency_contact_phone" class="form-label">Contact d'urgence - Téléphone</label>
                                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}">
                                            @error('emergency_contact_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergency_contact_relation" class="form-label">Lien avec le contact d'urgence</label>
                                            <select class="form-select @error('emergency_contact_relation') is-invalid @enderror" id="emergency_contact_relation" name="emergency_contact_relation">
                                                <option value="">Sélectionner un lien</option>
                                                <option value="parent" {{ old('emergency_contact_relation', $student->emergency_contact_relation) == 'parent' ? 'selected' : '' }}>Parent</option>
                                                <option value="frere/soeur" {{ old('emergency_contact_relation', $student->emergency_contact_relation) == 'frere/soeur' ? 'selected' : '' }}>Frère/Sœur</option>
                                                <option value="conjoint" {{ old('emergency_contact_relation', $student->emergency_contact_relation) == 'conjoint' ? 'selected' : '' }}>Conjoint</option>
                                                <option value="ami" {{ old('emergency_contact_relation', $student->emergency_contact_relation) == 'ami' ? 'selected' : '' }}>Ami</option>
                                                <option value="autre" {{ old('emergency_contact_relation', $student->emergency_contact_relation) == 'autre' ? 'selected' : '' }}>Autre</option>
                                            </select>
                                            @error('emergency_contact_relation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Adresse</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Votre adresse complète">{{ old('address', $student->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <form action="{{ route('student.profile.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="security">

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Conseil de sécurité :</strong> Utilisez un mot de passe fort contenant au moins 8 caractères, avec des lettres majuscules, minuscules, chiffres et caractères spéciaux.
                                </div>

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" minlength="8" required>
                                    <div class="form-text">Minimum 8 caractères</div>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Preferences Tab -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                            <form action="{{ route('student.profile.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="preferences">

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Notifications par email</h6>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ old('email_notifications', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_notifications">
                                                Notifications générales
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-3">Notifications push</h6>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="browser_notifications" name="browser_notifications" value="1" {{ old('browser_notifications', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="browser_notifications">
                                                Notifications du navigateur
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="language" class="form-label">Langue</label>
                                            <select class="form-select" id="language" name="language">
                                                <option value="fr" {{ old('language', 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                                                <option value="en" {{ old('language', 'fr') == 'en' ? 'selected' : '' }}>English</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="theme" class="form-label">Thème</label>
                                            <select class="form-select" id="theme" name="theme">
                                                <option value="light" {{ old('theme', 'light') == 'light' ? 'selected' : '' }}>Clair</option>
                                                <option value="dark" {{ old('theme', 'light') == 'dark' ? 'selected' : '' }}>Sombre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Enregistrer les préférences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Photo preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photo-preview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu" class="rounded-circle" width="120" height="120">`;
        };
        reader.readAsDataURL(file);
    }
});

// Password confirmation validation
document.getElementById('new_password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('new_password').value;
    const confirmation = this.value;

    if (password !== confirmation) {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection
