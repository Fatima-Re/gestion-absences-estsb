@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Prendre la présence',
        'actions' => '<a href="' . route('teacher.attendance.index') . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Feuille de présence</h5>
                </div>
                <div class="card-body">
                    <!-- Session Information -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Module:</strong><br>
                                {{ $session->module->name }}
                            </div>
                            <div class="col-md-3">
                                <strong>Groupe:</strong><br>
                                {{ $session->group->name }}
                            </div>
                            <div class="col-md-3">
                                <strong>Date:</strong><br>
                                {{ $session->date->format('d/m/Y') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Heure:</strong><br>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('teacher.attendance.store', $session) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Liste des étudiants ({{ $students->count() }})</h6>
                                <div>
                                    <button type="button" class="btn btn-sm btn-success" id="markAllPresent">
                                        <i class="fas fa-check"></i> Tous présents
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" id="markAllAbsent">
                                        <i class="fas fa-times"></i> Tous absents
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Nom</th>
                                        <th width="20%">Prénom</th>
                                        <th width="20%">Numéro étudiant</th>
                                        <th width="30%">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->user->name }}</td>
                                        <td>{{ $student->user->name }}</td>
                                        <td>{{ $student->student_number ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]" id="present_{{ $student->id }}" value="present" checked>
                                                <label class="btn btn-outline-success" for="present_{{ $student->id }}">Présent</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]" id="absent_{{ $student->id }}" value="absent">
                                                <label class="btn btn-outline-danger" for="absent_{{ $student->id }}">Absent</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]" id="excused_{{ $student->id }}" value="excused">
                                                <label class="btn btn-outline-warning" for="excused_{{ $student->id }}">Excusé</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]" id="late_{{ $student->id }}" value="late">
                                                <label class="btn btn-outline-info" for="late_{{ $student->id }}">Retard</label>
                                            </div>

                                            <!-- Late minutes input (shown only when late is selected) -->
                                            <div class="mt-2" id="late_minutes_{{ $student->id }}" style="display: none;">
                                                <label for="late_minutes_input_{{ $student->id }}" class="form-label small">Minutes de retard:</label>
                                                <input type="number" class="form-control form-control-sm" id="late_minutes_input_{{ $student->id }}" name="attendance[{{ $student->id }}][late_minutes]" min="1" max="300" placeholder="15">
                                            </div>

                                            <!-- Comments input -->
                                            <div class="mt-2">
                                                <input type="text" class="form-control form-control-sm" name="attendance[{{ $student->id }}][comments]" placeholder="Commentaires (optionnel)">
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Rappel</h6>
                            <ul class="mb-0">
                                <li>Une fois la feuille de présence enregistrée, elle ne peut plus être modifiée après 48 heures.</li>
                                <li>Les étudiants absents recevront automatiquement une notification.</li>
                                <li>Assurez-vous de vérifier l'identité des étudiants présents.</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Enregistrer la présence
                                </button>
                                <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary btn-lg ms-3">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle late minutes visibility
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const studentId = this.name.match(/\[(\d+)\]/)[1];
        const lateMinutesDiv = document.getElementById(`late_minutes_${studentId}`);
        const lateMinutesInput = document.getElementById(`late_minutes_input_${studentId}`);

        if (this.value === 'late') {
            lateMinutesDiv.style.display = 'block';
            lateMinutesInput.required = true;
        } else {
            lateMinutesDiv.style.display = 'none';
            lateMinutesInput.required = false;
            lateMinutesInput.value = '';
        }
    });
});

// Mark all present
document.getElementById('markAllPresent').addEventListener('click', function() {
    document.querySelectorAll('input[value="present"]').forEach(radio => {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    });
});

// Mark all absent
document.getElementById('markAllAbsent').addEventListener('click', function() {
    document.querySelectorAll('input[value="absent"]').forEach(radio => {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    let hasErrors = false;

    // Check if at least one attendance record is filled
    const attendanceRecords = document.querySelectorAll('input[name*="[status]"]:checked');
    if (attendanceRecords.length === 0) {
        alert('Veuillez prendre la présence pour au moins un étudiant.');
        e.preventDefault();
        return;
    }

    // Check late minutes for late students
    document.querySelectorAll('input[value="late"]:checked').forEach(lateRadio => {
        const studentId = lateRadio.name.match(/\[(\d+)\]/)[1];
        const lateMinutesInput = document.getElementById(`late_minutes_input_${studentId}`);

        if (!lateMinutesInput.value || lateMinutesInput.value < 1) {
            alert(`Veuillez spécifier le nombre de minutes de retard pour l'étudiant ${studentId}.`);
            hasErrors = true;
        }
    });

    if (hasErrors) {
        e.preventDefault();
    }
});
</script>
@endsection
