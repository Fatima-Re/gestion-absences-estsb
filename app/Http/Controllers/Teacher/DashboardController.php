<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Absence;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display teacher dashboard
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        
        // Today's sessions
        $todaySessions = CourseSession::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->where('status', CourseSession::STATUS_SCHEDULED)
            ->with(['module', 'group'])
            ->orderBy('start_time')
            ->get();
        
        // Upcoming sessions (next 5)
        $upcomingSessions = CourseSession::where('teacher_id', $teacher->id)
            ->whereDate('date', '>', today())
            ->where('status', CourseSession::STATUS_SCHEDULED)
            ->with(['module', 'group'])
            ->orderBy('date')
            ->take(5)
            ->get();
        
        // Recent absences for teacher's sessions
        $recentAbsences = Absence::whereHas('session', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['student.user', 'session.module'])
            ->latest()
            ->take(10)
            ->get();
        
        // Statistics
        $stats = [
            'modules_count' => $teacher->modules()->count(),
            'sessions_this_month' => CourseSession::where('teacher_id', $teacher->id)
                ->whereMonth('date', now()->month)
                ->count(),
            'pending_attendance' => CourseSession::where('teacher_id', $teacher->id)
                ->whereDate('date', '<', today())
                ->whereDoesntHave('attendanceRecords')
                ->count(),
        ];
        
        return view('teacher.dashboard.index', compact(
            'todaySessions',
            'upcomingSessions',
            'recentAbsences',
            'stats'
        ));
    }
}