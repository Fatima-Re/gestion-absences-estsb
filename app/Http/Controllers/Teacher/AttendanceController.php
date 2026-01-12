<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\AttendanceRecord;
use App\Models\Absence;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display attendance history with filters
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        $query = CourseSession::where('teacher_id', $teacher->id)
            ->whereHas('attendanceRecords')
            ->with(['module', 'group']);
        
        // Apply filters
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        
        $sessions = $query->orderBy('date', 'desc')->paginate(20);
        $modules = $teacher->modules;
        $groups = $teacher->groups();
        
        return view('teacher.attendance.index', compact('sessions', 'modules', 'groups'));
    }

    /**
     * Show form for taking attendance
     */
    public function create(CourseSession $session)
    {
        $this->authorizeSession($session);
        
        // Check if attendance already taken
        if ($session->attendanceRecords()->exists()) {
            return redirect()->route('teacher.attendance.show', $session)
                ->with('info', 'La présence a déjà été prise pour cette séance.');
        }
        
        $students = $session->group->activeStudents()->with('user')->get();
        
        return view('teacher.attendance.create', compact('session', 'students'));
    }

    /**
     * Store attendance for a session
     */
    public function store(Request $request, CourseSession $session)
    {
        $this->authorizeSession($session);
        $this->validateModificationPeriod($session);
        
        $validator = $this->validateAttendanceData($request);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Process attendance records
        $this->processAttendance($session, $request->attendance);
        
        // Update session status
        $session->update(['status' => CourseSession::STATUS_COMPLETED]);
        
        return redirect()->route('teacher.schedule.index')
            ->with('success', 'Feuille de présence enregistrée avec succès.');
    }

    /**
     * Display attendance for a session
     */
    public function show(CourseSession $session)
    {
        $this->authorizeSession($session);
        
        $attendanceRecords = $session->attendanceRecords()
            ->with(['student.user'])
            ->get();
        
        return view('teacher.attendance.show', compact('session', 'attendanceRecords'));
    }

    /**
     * Show form for editing attendance
     */
    public function edit(CourseSession $session)
    {
        $this->authorizeSession($session);
        $this->validateModificationPeriod($session);
        
        $attendanceRecords = $session->attendanceRecords()
            ->with(['student.user'])
            ->get()
            ->keyBy('student_id');
        
        $students = $session->group->activeStudents()->with('user')->get();
        
        return view('teacher.attendance.edit', compact('session', 'attendanceRecords', 'students'));
    }

    /**
     * Update attendance for a session
     */
    public function update(Request $request, CourseSession $session)
    {
        $this->authorizeSession($session);
        $this->validateModificationPeriod($session);
        
        $validator = $this->validateAttendanceData($request);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Update attendance records
        $this->updateAttendanceRecords($session, $request->attendance);
        
        // Log the modification
        $this->logAttendanceModification($session, $request);
        
        return redirect()->route('teacher.attendance.show', $session)
            ->with('success', 'Feuille de présence mise à jour avec succès.');
    }

    /**
     * Check if teacher is authorized for this session
     */
    private function authorizeSession(CourseSession $session)
    {
        if ($session->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Non autorisé.');
        }
    }

    /**
     * Validate modification period (48 hours)
     */
    private function validateModificationPeriod(CourseSession $session)
    {
        if ($session->date->diffInHours(now()) > 48) {
            abort(403, 'La période de modification est expirée (48h après la séance).');
        }
    }

    /**
     * Validate attendance data
     */
    private function validateAttendanceData(Request $request)
    {
        return Validator::make($request->all(), [
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,excused,late',
            'attendance.*.comments' => 'nullable|string|max:255',
            'attendance.*.late_minutes' => 'required_if:attendance.*.status,late|nullable|integer|min:1',
        ]);
    }

    /**
     * Process and store attendance records
     */
    private function processAttendance(CourseSession $session, $attendanceData)
    {
        $attendanceRecords = [];
        $absences = [];
        
        foreach ($attendanceData as $studentId => $data) {
            $attendanceRecords[] = [
                'session_id' => $session->id,
                'student_id' => $studentId,
                'status' => $data['status'],
                'comments' => $data['comments'] ?? null,
                'recorded_by' => Auth::user()->teacher->id,
                'recorded_at' => now(),
                'is_late' => $data['status'] === 'late',
                'late_minutes' => $data['late_minutes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Create absence if student is absent
            if ($data['status'] === 'absent') {
                $absences[] = [
                    'student_id' => $studentId,
                    'session_id' => $session->id,
                    'status' => Absence::STATUS_UNJUSTIFIED,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Notify student
                $this->notifyStudentOfAbsence($studentId, $session);
            }
        }
        
        // Bulk insert for performance
        AttendanceRecord::insert($attendanceRecords);
        
        if (!empty($absences)) {
            Absence::insert($absences);
        }
    }

    /**
     * Update existing attendance records
     */
    private function updateAttendanceRecords(CourseSession $session, $attendanceData)
    {
        foreach ($attendanceData as $studentId => $data) {
            $attendanceRecord = AttendanceRecord::where('session_id', $session->id)
                ->where('student_id', $studentId)
                ->first();
            
            if ($attendanceRecord) {
                $oldStatus = $attendanceRecord->status;
                $newStatus = $data['status'];
                
                $attendanceRecord->update([
                    'status' => $newStatus,
                    'comments' => $data['comments'] ?? null,
                    'is_late' => $newStatus === 'late',
                    'late_minutes' => $data['late_minutes'] ?? null,
                ]);
                
                // Update absence record if status changed
                $this->updateAbsenceRecord($studentId, $session->id, $oldStatus, $newStatus);
            } else {
                // Create new record
                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                    'status' => $data['status'],
                    'comments' => $data['comments'] ?? null,
                    'recorded_by' => Auth::user()->teacher->id,
                    'recorded_at' => now(),
                    'is_late' => $data['status'] === 'late',
                    'late_minutes' => $data['late_minutes'] ?? null,
                ]);
                
                if ($data['status'] === 'absent') {
                    Absence::create([
                        'student_id' => $studentId,
                        'session_id' => $session->id,
                        'status' => Absence::STATUS_UNJUSTIFIED,
                    ]);
                    
                    $this->notifyStudentOfAbsence($studentId, $session);
                }
            }
        }
    }

    /**
     * Update absence record based on attendance status change
     */
    private function updateAbsenceRecord($studentId, $sessionId, $oldStatus, $newStatus)
    {
        if ($oldStatus === 'absent' && $newStatus !== 'absent') {
            // Remove absence
            Absence::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->delete();
        } elseif ($oldStatus !== 'absent' && $newStatus === 'absent') {
            // Create absence
            Absence::create([
                'student_id' => $studentId,
                'session_id' => $sessionId,
                'status' => Absence::STATUS_UNJUSTIFIED,
            ]);
            
            $this->notifyStudentOfAbsence($studentId, CourseSession::find($sessionId));
        }
    }

    /**
     * Notify student of their absence
     */
    private function notifyStudentOfAbsence($studentId, $session)
    {
        $student = \App\Models\Student::find($studentId);
        
        if ($student && $student->user) {
            Notification::create([
                'user_id' => $student->user_id,
                'title' => 'Absence enregistrée',
                'message' => 'Vous avez été marqué absent pour la séance du ' . $session->date->format('d/m/Y'),
                'type' => Notification::TYPE_WARNING,
                'related_model' => 'Absence',
                'related_id' => null,
            ]);
        }
    }

    /**
     * Log attendance modification
     */
    private function logAttendanceModification($session, $request)
    {
        // You can implement an activity log here
        // Example: ActivityLog::create([...]);
    }
}