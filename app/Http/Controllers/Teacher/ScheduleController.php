<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Module;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// For Excel exports
use Maatwebsite\Excel\Facades\Excel;

// For PDF exports
use Barryvdh\DomPDF\Facade\Pdf;

// Your export classes
use App\Exports\AbsencesExport;
use App\Exports\AttendanceExport;
use App\Exports\StatisticsExport;
use App\Exports\StudentsExport;
use App\Exports\ReportExport;   

class ScheduleController extends Controller
{
    /**
     * Display teacher's schedule with optional filters
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        // Build query for teacher's sessions
        $query = CourseSession::where('teacher_id', $teacher->id)
            ->with(['module', 'group']);
        
        // Apply date filter
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        // Apply module filter
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply period filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        
        // Order by date and time
        $sessions = $query->orderBy('date')->orderBy('start_time')->paginate(20);
        
        // Get filter options
        $modules = $teacher->modules;
        $groups = $teacher->groups();
        
        return view('teacher.schedule.index', compact('sessions', 'modules', 'groups'));
    }

    /**
     * Display details of a specific session
     */
    public function show(CourseSession $session)
    {
        // Verify teacher has access to this session
        $this->authorizeTeacherSession($session);
        
        $session->load(['module', 'group', 'attendanceRecords.student.user']);
        
        return view('teacher.schedule.show', compact('session'));
    }

    /**
     * Cancel a scheduled session
     */
    public function cancel(Request $request, CourseSession $session)
    {
        // Verify teacher has access to this session
        $this->authorizeTeacherSession($session);
        
        // Validate cancellation reason
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        // Update session status
        $session->update([
            'status' => CourseSession::STATUS_CANCELLED,
            'is_cancelled' => true,
            'cancellation_reason' => $request->reason,
        ]);
        
        // Notify students in the group
        $this->notifyStudentsOfCancellation($session, $request->reason);
        
        return redirect()->route('teacher.schedule.index')
            ->with('success', 'Séance annulée avec succès.');
    }

    /**
     * Get teacher's calendar events (for calendar view)
     */
    public function calendar(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        $events = CourseSession::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$request->start ?? now()->startOfMonth(), $request->end ?? now()->endOfMonth()])
            ->with(['module', 'group'])
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->module->name . ' - ' . $session->group->name,
                    'start' => $session->date->format('Y-m-d') . 'T' . $session->start_time->format('H:i:s'),
                    'end' => $session->date->format('Y-m-d') . 'T' . $session->end_time->format('H:i:s'),
                    'className' => $this->getSessionClass($session),
                    'extendedProps' => [
                        'module' => $session->module->name,
                        'group' => $session->group->name,
                        'room' => $session->room,
                        'status' => $session->status,
                    ],
                ];
            });
        
        return response()->json($events);
    }

    /**
     * Verify teacher has access to the session
     */
    private function authorizeTeacherSession(CourseSession $session)
    {
        if ($session->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Non autorisé.');
        }
    }

    /**
     * Notify students of session cancellation
     */
    private function notifyStudentsOfCancellation(CourseSession $session, $reason)
    {
        $students = $session->group->students;
        
        foreach ($students as $student) {
            \App\Models\Notification::create([
                'user_id' => $student->user_id,
                'title' => 'Séance annulée',
                'message' => 'La séance du ' . $session->date->format('d/m/Y') . ' (' . $session->module->name . ') a été annulée. Raison: ' . $reason,
                'type' => \App\Models\Notification::TYPE_INFO,
                'related_model' => 'CourseSession',
                'related_id' => $session->id,
            ]);
        }
    }

    /**
     * Get CSS class for session based on status
     */
    private function getSessionClass(CourseSession $session)
    {
        switch ($session->status) {
            case CourseSession::STATUS_CANCELLED:
                return 'bg-danger';
            case CourseSession::STATUS_COMPLETED:
                return 'bg-success';
            case CourseSession::STATUS_SCHEDULED:
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }
}