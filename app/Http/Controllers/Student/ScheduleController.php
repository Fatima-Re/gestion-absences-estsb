<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display student's schedule
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        $currentWeek = (int) $request->get('week', 0);
        $today = now();
        $startOfWeek = $today->copy()->startOfWeek()->addWeeks($currentWeek);
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        // If student or group missing, return empty schedule safely
        if (!$student || !$student->group_id) {
            $schedule = collect();
            $modules = collect();
            return view('student.schedule.index', compact('schedule', 'modules', 'startOfWeek', 'endOfWeek', 'currentWeek'));
        }

        // Build query for student's schedule
        $query = CourseSession::where('group_id', $student->group_id)
            ->with(['module', 'teacher.user']);

        // Filter by date (single day) or default to current week
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        } else {
            $query->whereBetween('start_time', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()]);
        }

        // Filter by module
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by custom period
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('start_time', [$request->date_from, $request->date_to]);
        }

        // Order by date and time
        $schedule = $query->orderBy('start_time')->get();

        // Ensure sessions expose a date string used by the view
        $schedule = $schedule->map(function ($session) {
            $session->date = Carbon::parse($session->start_time)->format('Y-m-d');
            return $session;
        });

        // Get filter options
        $modules = $student->group ? $student->group->modules : collect();

        return view('student.schedule.index', compact('schedule', 'modules', 'startOfWeek', 'endOfWeek', 'currentWeek'));
    }

    /**
     * Display student's missed sessions
     */
    public function missedSessions(Request $request)
    {
        $student = Auth::user()->student;

        if (!$student || !$student->group_id) {
            $missedSessions = collect();
            $modules = collect();
            return view('student.schedule.missed', compact('missedSessions', 'modules'));
        }

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
            $query->whereBetween('start_time', [$request->date_from, $request->date_to]);
        }
        
        // Order by date (most recent first)
        $missedSessions = $query->orderBy('start_time', 'desc')->paginate(20);
        
        // Get filter options
        $modules = $student->group ? $student->group->modules : collect();

        return view('student.schedule.missed', compact('missedSessions', 'modules'));
    }

    /**
     * Display student's calendar (for calendar view)
     */
    public function calendar(Request $request)
    {
        $student = Auth::user()->student;

        if (!$student || !$student->group_id) {
            return response()->json([]);
        }

        // Get sessions for calendar
        $events = CourseSession::where('group_id', $student->group_id)
            ->whereBetween('start_time', [$request->start ?? now()->startOfMonth(), $request->end ?? now()->endOfMonth()])
            ->with(['module', 'teacher.user', 'absences'])
            ->get()
            ->map(function ($session) use ($student) {
                // Check if student was absent for this session
                $isAbsent = $session->absences()
                    ->where('student_id', $student->id)
                    ->exists();

                return [
                    'id' => $session->id,
                    'title' => $session->module->name . ' - ' . ($session->teacher->user->name ?? ''),
                    'start' => Carbon::parse($session->start_time)->toIso8601String(),
                    'end' => Carbon::parse($session->end_time)->toIso8601String(),
                    'className' => $this->getSessionClass($session, $isAbsent),
                    'extendedProps' => [
                        'module' => $session->module->name,
                        'teacher' => $session->teacher->user->name ?? '',
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

        if (!$student || !$student->group_id) {
            return response()->json([]);
        }

        $upcomingSessions = CourseSession::where('group_id', $student->group_id)
            ->whereDate('start_time', '>=', today())
            ->where('status', CourseSession::STATUS_SCHEDULED)
            ->with(['module', 'teacher.user'])
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