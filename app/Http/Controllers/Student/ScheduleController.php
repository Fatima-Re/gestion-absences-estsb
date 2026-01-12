<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Display student's schedule
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        // Build query for student's schedule
        $query = CourseSession::where('group_id', $student->group_id)
            ->with(['module', 'teacher.user']);
        
        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        // Filter by module
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by period
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        
        // Order by date and time
        $sessions = $query->orderBy('date')->orderBy('start_time')->paginate(20);
        
        // Get filter options
        $modules = $student->group->modules;
        
        return view('student.schedule.index', compact('sessions', 'modules'));
    }

    /**
     * Display student's missed sessions
     */
    public function missedSessions(Request $request)
    {
        $student = Auth::user()->student;
        
        // Build query for missed sessions (sessions where student was absent)
        $query = CourseSession::where('group_id', $student->group_id)
            ->whereHas('absences', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['module', 'teacher.user', 'absences' => function($q) use ($student) {
                $q->where('student_id', $student->id)->with('justification');
            }]);
        
        // Apply filters
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        
        // Order by date (most recent first)
        $missedSessions = $query->orderBy('date', 'desc')->paginate(20);
        
        // Get filter options
        $modules = $student->group->modules;
        
        return view('student.schedule.missed', compact('missedSessions', 'modules'));
    }

    /**
     * Display student's calendar (for calendar view)
     */
    public function calendar(Request $request)
    {
        $student = Auth::user()->student;
        
        // Get sessions for calendar
        $events = CourseSession::where('group_id', $student->group_id)
            ->whereBetween('date', [$request->start ?? now()->startOfMonth(), $request->end ?? now()->endOfMonth()])
            ->with(['module', 'teacher.user'])
            ->get()
            ->map(function ($session) use ($student) {
                // Check if student was absent for this session
                $isAbsent = $session->absences()
                    ->where('student_id', $student->id)
                    ->exists();
                
                return [
                    'id' => $session->id,
                    'title' => $session->module->name . ' - ' . $session->teacher->user->name,
                    'start' => $session->date->format('Y-m-d') . 'T' . $session->start_time->format('H:i:s'),
                    'end' => $session->date->format('Y-m-d') . 'T' . $session->end_time->format('H:i:s'),
                    'className' => $this->getSessionClass($session, $isAbsent),
                    'extendedProps' => [
                        'module' => $session->module->name,
                        'teacher' => $session->teacher->user->name,
                        'room' => $session->room,
                        'status' => $session->status,
                        'is_absent' => $isAbsent,
                    ],
                ];
            });
        
        return response()->json($events);
    }

    /**
     * Get upcoming sessions (for dashboard widget)
     */
    public function upcoming()
    {
        $student = Auth::user()->student;
        
        $upcomingSessions = CourseSession::where('group_id', $student->group_id)
            ->whereDate('date', '>=', today())
            ->where('status', CourseSession::STATUS_SCHEDULED)
            ->with(['module', 'teacher.user'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(10)
            ->get();
        
        return response()->json($upcomingSessions);
    }

    /**
     * Get CSS class for session based on status and attendance
     */
    private function getSessionClass($session, $isAbsent)
    {
        if ($session->status === CourseSession::STATUS_CANCELLED) {
            return 'bg-secondary';
        }
        
        if ($isAbsent) {
            return 'bg-danger';
        }
        
        if ($session->status === CourseSession::STATUS_COMPLETED) {
            return 'bg-success';
        }
        
        return 'bg-primary';
    }
}