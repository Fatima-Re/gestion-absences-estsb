<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Justification;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JustificationController extends Controller
{
    /**
     * Display student's justifications
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Justification::where('student_id', $student->id)
            ->with(['absence.session.module']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        $justifications = $query->latest()->paginate(20);
        
        return view('student.justifications.index', compact('justifications'));
    }

    /**
     * Show form for creating a justification
     */
    public function create(Absence $absence)
    {
        // Verify ownership
        if ($absence->student_id !== Auth::user()->student->id) {
            abort(403, 'Non autorisé.');
        }
        
        // Check if justifiable
        if (!$absence->isJustifiable()) {
            return back()->withErrors([
                'general' => 'La période de justification est expirée (7 jours après l\'absence).'
            ]);
        }
        
        // Check if already justified
        if ($absence->status === Absence::STATUS_JUSTIFIED) {
            return back()->with('info', 'Cette absence est déjà justifiée.');
        }
        
        // Check if justification pending
        if ($absence->justification && $absence->justification->status === Justification::STATUS_PENDING) {
            return back()->with('info', 'Une justification est déjà en attente de validation.');
        }
        
        $justificationTypes = [
            Justification::TYPE_MEDICAL => 'Certificat médical',
            Justification::TYPE_OFFICIAL => 'Convocation officielle',
            Justification::TYPE_PERSONAL => 'Raison personnelle',
            Justification::TYPE_TRANSPORT => 'Problème de transport',
            Justification::TYPE_OTHER => 'Autre',
        ];
        
        return view('student.justifications.create', compact('absence', 'justificationTypes'));
    }

    /**
     * Store a new justification
     */
    public function store(Request $request)
    {
        $request->validate([
            'absence_id' => 'required|exists:absences,id',
            'type' => 'required|in:medical,official,personal,transport,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $student = Auth::user()->student;
        $absence = Absence::findOrFail($request->absence_id);
        
        // Verify ownership
        if ($absence->student_id !== $student->id) {
            abort(403, 'Non autorisé.');
        }
        
        // Check if justifiable
        if (!$absence->isJustifiable()) {
            return back()->withErrors([
                'general' => 'La période de justification est expirée.'
            ]);
        }
        
        // Validate medical certificate submission period
        if ($request->type === Justification::TYPE_MEDICAL) {
            $validityDays = Setting::getMedicalCertificateValidity();
            $endDate = \Carbon\Carbon::parse($request->end_date);
            
            if ($endDate->diffInDays(now()) > $validityDays) {
                return back()->withErrors([
                    'end_date' => "Le certificat médical doit être soumis dans les $validityDays jours après le retour."
                ]);
            }
        }
        
        // Upload file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('justifications', $fileName, 'public');
        
        // Create justification
        $justification = Justification::create([
            'student_id' => $student->id,
            'absence_id' => $absence->id,
            'type' => $request->type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime' => $file->getMimeType(),
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'submitted_at' => now(),
            'status' => Justification::STATUS_PENDING,
        ]);
        
        // Update absence status
        $absence->update([
            'status' => Absence::STATUS_PENDING,
            'justification_id' => $justification->id,
        ]);
        
        // Notify administrators
        $this->notifyAdmins($justification);
        
        return redirect()->route('student.justifications.index')
            ->with('success', 'Justification soumise avec succès. En attente de validation.');
    }

    /**
     * Display a justification
     */
    public function show(Justification $justification)
    {
        // Verify ownership
        if ($justification->student_id !== Auth::user()->student->id) {
            abort(403, 'Non autorisé.');
        }
        
        $justification->load(['absence.session.module', 'admin']);
        
        return view('student.justifications.show', compact('justification'));
    }

    /**
     * Notify administrators about new justification
     */
    private function notifyAdmins(Justification $justification)
    {
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Nouvelle justification soumise',
                'message' => $justification->student->user->name . ' a soumis une justification.',
                'type' => Notification::TYPE_INFO,
                'priority' => Notification::PRIORITY_NORMAL,
                'related_model' => 'Justification',
                'related_id' => $justification->id,
                'action_url' => route('admin.justifications.show', $justification),
            ]);
        }
    }
}