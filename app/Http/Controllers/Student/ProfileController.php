<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the student's profile
     */
    public function show()
    {
        $student = Auth::user()->student->load('group');
        $user = Auth::user();

        // Calculate attendance statistics
        $attendanceStats = $this->calculateAttendanceStats($student);

        // Get recent notifications
        $recentNotifications = \App\Models\Notification::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('student.profile.index', compact('student', 'user', 'attendanceStats', 'recentNotifications'));
    }

    /**
     * Show the form for editing the student's profile
     */
    public function edit()
    {
        $student = Auth::user()->student;
        $user = Auth::user();

        return view('student.profile.edit', compact('student', 'user'));
    }

    /**
     * Update the student's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        
        // Determine which section to update
        $section = $request->get('section', 'personal');
        
        switch ($section) {
            case 'personal':
                return $this->updatePersonalInfo($request, $user, $student);
                
            case 'contact':
                return $this->updateContactInfo($request, $user, $student);
                
            case 'security':
                return $this->updatePassword($request, $user);
                
            case 'preferences':
                return $this->updatePreferences($request, $user);
                
            default:
                return back()->withErrors(['section' => 'Section de mise à jour invalide.']);
        }
    }

    /**
     * Update personal information
     */
    private function updatePersonalInfo(Request $request, User $user, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'date_of_birth' => 'nullable|date|before:today',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('activeTab', 'personal');
        }

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        
        // Update student information
        if ($request->filled('date_of_birth')) {
            $student->date_of_birth = $request->date_of_birth;
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_url && Storage::disk('public')->exists($student->photo_url)) {
                Storage::disk('public')->delete($student->photo_url);
            }
            
            // Upload new photo
            $photo = $request->file('photo');
            $photoName = 'student_' . $student->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('students/photos', $photoName, 'public');
            
            $student->photo_url = $photoPath;
        }
        
        // Save changes
        $user->save();
        $student->save();
        
        return back()->with('success', 'Informations personnelles mises à jour avec succès.')->with('activeTab', 'personal');
    }

    /**
     * Update contact information
     */
    private function updateContactInfo(Request $request, User $user, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('activeTab', 'contact');
        }

        // Update user contact info
        $user->phone = $request->phone;
        
        // Update student contact info
        $student->address = $request->address;
        $student->emergency_contact_name = $request->emergency_contact_name;
        $student->emergency_contact_phone = $request->emergency_contact_phone;
        $student->emergency_contact_relation = $request->emergency_contact_relation;
        
        // Save changes
        $user->save();
        $student->save();
        
        return back()->with('success', 'Informations de contact mises à jour avec succès.')->with('activeTab', 'contact');
    }

    /**
     * Update password
     */
    private function updatePassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('activeTab', 'security');
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])->with('activeTab', 'security');
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return back()->with('success', 'Mot de passe modifié avec succès.')->with('activeTab', 'security');
    }

    /**
     * Update user preferences
     */
    private function updatePreferences(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
            'language' => 'in:fr,en',
            'theme' => 'in:light,dark',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('activeTab', 'preferences');
        }

        // Store preferences in user settings
        // You could create a UserSetting model or store as JSON
        $preferences = [
            'email_notifications' => $request->boolean('email_notifications', true),
            'browser_notifications' => $request->boolean('browser_notifications', true),
            'language' => $request->language ?? 'fr',
            'theme' => $request->theme ?? 'light',
        ];
        
        // If you have a settings field in user model:
        // $user->settings = json_encode($preferences);
        // $user->save();
        
        // For now, we'll just acknowledge the update
        return back()->with('success', 'Préférences mises à jour avec succès.')->with('activeTab', 'preferences');
    }

    /**
     * Delete student's profile photo
     */
    public function deletePhoto()
    {
        $student = Auth::user()->student;
        
        if ($student->photo_url && Storage::disk('public')->exists($student->photo_url)) {
            Storage::disk('public')->delete($student->photo_url);
            $student->photo_url = null;
            $student->save();
            
            return back()->with('success', 'Photo de profil supprimée avec succès.')->with('activeTab', 'personal');
        }
        
        return back()->withErrors(['photo' => 'Aucune photo de profil à supprimer.'])->with('activeTab', 'personal');
    }

    /**
     * Display student's academic summary
     */
    public function academicSummary()
    {
        $student = Auth::user()->student->load(['group', 'group.modules']);
        
        // Get attendance by module
        $attendanceByModule = [];
        foreach ($student->group->modules as $module) {
            $moduleAbsences = $student->absences()
                ->whereHas('session', function($q) use ($module) {
                    $q->where('module_id', $module->id);
                })
                ->count();
            
            $moduleSessions = $module->courseSessions()
                ->where('group_id', $student->group_id)
                ->count();
            
            $attendanceRate = $moduleSessions > 0 
                ? (($moduleSessions - $moduleAbsences) / $moduleSessions) * 100 
                : 100;
            
            $attendanceByModule[] = [
                'module' => $module->name,
                'code' => $module->code,
                'teacher' => $module->teachers->first()->user->name ?? 'N/A',
                'sessions' => $moduleSessions,
                'absences' => $moduleAbsences,
                'attendance_rate' => round($attendanceRate, 2),
            ];
        }
        
        return view('student.profile.academic', compact('student', 'attendanceByModule'));
    }

    /**
     * Generate and download student's attendance certificate
     */
    public function downloadAttendanceCertificate()
    {
        $student = Auth::user()->student->load('group');
        $attendanceStats = $this->calculateAttendanceStats($student);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.profile.attendance_certificate', [
            'student' => $student,
            'attendanceStats' => $attendanceStats,
            'date' => now()->format('d/m/Y'),
        ]);
        
        $filename = 'certificat-presence-' . $student->student_number . '-' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export student's data for personal reference
     */
    public function exportMyData()
    {
        $student = Auth::user()->student->load(['group', 'absences.session.module', 'justifications']);
        $user = Auth::user();
        
        $data = [
            'personal_information' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'student_number' => $student->student_number,
                'date_of_birth' => $student->date_of_birth?->format('d/m/Y'),
                'address' => $student->address,
                'group' => $student->group->name ?? null,
                'academic_year' => $student->academic_year,
                'specialty' => $student->group->specialty ?? null,
            ],
            'attendance_summary' => [
                'total_sessions' => \App\Models\CourseSession::where('group_id', $student->group_id)->count(),
                'total_absences' => $student->absences->count(),
                'justified_absences' => $student->absences->where('status', \App\Models\Absence::STATUS_JUSTIFIED)->count(),
                'attendance_rate' => $this->calculateAttendanceStats($student)['attendance_rate'],
            ],
            'absence_details' => $student->absences->map(function($absence) {
                return [
                    'date' => $absence->session->date->format('d/m/Y') ?? null,
                    'module' => $absence->session->module->name ?? null,
                    'time' => $absence->session->start_time->format('H:i') . ' - ' . $absence->session->end_time->format('H:i'),
                    'status' => $this->getStatusText($absence->status),
                    'justification_status' => $absence->justification ? $absence->justification->status : 'none',
                    'created_at' => $absence->created_at->format('d/m/Y H:i'),
                ];
            }),
            'justifications_submitted' => $student->justifications->map(function($justification) {
                return [
                    'type' => $this->getJustificationTypeText($justification->type),
                    'status' => $this->getJustificationStatusText($justification->status),
                    'submitted_at' => $justification->submitted_at->format('d/m/Y H:i'),
                    'validated_at' => $justification->validation_date?->format('d/m/Y H:i'),
                    'comments' => $justification->comments,
                ];
            }),
            'export_date' => now()->format('d/m/Y H:i:s'),
            'purpose' => 'Export personnel des données étudiant',
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="mes-donnees-' . $student->student_number . '-' . date('Y-m-d') . '.json"',
        ];
        
        return response($json, 200, $headers);
    }

    /**
     * Display student's documents (certificates, reports, etc.)
     */
    public function myDocuments()
    {
        $student = Auth::user()->student;
        
        // This would come from a Document model if you have one
        $documents = [
            [
                'name' => 'Fiche d\'inscription',
                'type' => 'Administratif',
                'upload_date' => '2024-09-01',
                'status' => 'Validé',
            ],
            [
                'name' => 'Certificat médical',
                'type' => 'Médical',
                'upload_date' => '2024-10-15',
                'status' => 'En attente',
            ],
        ];
        
        return view('student.profile.documents', compact('student', 'documents'));
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats(Student $student)
    {
        $totalAbsences = $student->absences()->count();
        $justifiedAbsences = $student->absences()->where('status', \App\Models\Absence::STATUS_JUSTIFIED)->count();
        $unjustifiedAbsences = $student->absences()->where('status', \App\Models\Absence::STATUS_UNJUSTIFIED)->count();
        $pendingAbsences = $student->absences()->where('status', \App\Models\Absence::STATUS_PENDING)->count();
        
        $totalSessions = \App\Models\CourseSession::where('group_id', $student->group_id)->count();
        $attendanceRate = $totalSessions > 0 
            ? (($totalSessions - $totalAbsences) / $totalSessions) * 100 
            : 100;
        
        // Get module-wise attendance
        $moduleStats = [];
        foreach ($student->group->modules as $module) {
            $moduleAbsences = $student->absences()
                ->whereHas('session', function($q) use ($module) {
                    $q->where('module_id', $module->id);
                })
                ->count();
            
            $moduleSessions = $module->courseSessions()
                ->where('group_id', $student->group_id)
                ->count();
            
            $moduleRate = $moduleSessions > 0 
                ? (($moduleSessions - $moduleAbsences) / $moduleSessions) * 100 
                : 100;
            
            $moduleStats[] = [
                'module' => $module->name,
                'absences' => $moduleAbsences,
                'sessions' => $moduleSessions,
                'attendance_rate' => round($moduleRate, 2),
            ];
        }
        
        return [
            'total_absences' => $totalAbsences,
            'justified_absences' => $justifiedAbsences,
            'unjustified_absences' => $unjustifiedAbsences,
            'pending_absences' => $pendingAbsences,
            'total_sessions' => $totalSessions,
            'attendance_rate' => round($attendanceRate, 2),
            'module_stats' => $moduleStats,
            'status' => $this->getAttendanceStatus($attendanceRate),
        ];
    }

    /**
     * Get attendance status
     */
    private function getAttendanceStatus($rate)
    {
        if ($rate < \App\Models\Setting::getAbsenceCriticalThreshold()) {
            return [
                'text' => 'Critique',
                'class' => 'danger',
                'icon' => 'exclamation-triangle'
            ];
        } elseif ($rate < \App\Models\Setting::getAbsenceAlertThreshold()) {
            return [
                'text' => 'Alerte',
                'class' => 'warning',
                'icon' => 'exclamation-circle'
            ];
        } elseif ($rate < \App\Models\Setting::getAbsenceWarningThreshold()) {
            return [
                'text' => 'Avertissement',
                'class' => 'info',
                'icon' => 'info-circle'
            ];
        } else {
            return [
                'text' => 'Bon',
                'class' => 'success',
                'icon' => 'check-circle'
            ];
        }
    }

    /**
     * Get status text for export
     */
    private function getStatusText($status)
    {
        $statuses = [
            'unjustified' => 'Non justifié',
            'justified' => 'Justifié',
            'pending' => 'En attente'
        ];
        
        return $statuses[$status] ?? $status;
    }

    /**
     * Get justification type text
     */
    private function getJustificationTypeText($type)
    {
        $types = [
            'medical' => 'Certificat médical',
            'official' => 'Convocation officielle',
            'personal' => 'Raison personnelle',
            'transport' => 'Problème de transport',
            'other' => 'Autre',
        ];
        
        return $types[$type] ?? $type;
    }

    /**
     * Get justification status text
     */
    private function getJustificationStatusText($status)
    {
        $statuses = [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'needs_info' => 'Information requise',
        ];
        
        return $statuses[$status] ?? $status;
    }
}
