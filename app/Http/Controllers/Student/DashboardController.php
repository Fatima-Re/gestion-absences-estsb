<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\CourseSession;
use App\Models\Justification;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display student dashboard
     */
    public function index()
    {
        $student = Auth::user()->student;
        
        // Recent absences
        $recentAbsences = Absence::where('student_id', $student->id)
            ->with(['session.module', 'justification'])
            ->latest()
            ->take(5)
            ->get();
        
        // Today's sessions
        $todaySessions = CourseSession::where('group_id', $student->group_id)
            ->whereDate('date', today())
            ->where('status', CourseSession::STATUS_SCHEDULED)
            ->with('module')
            ->orderBy('start_time')
            ->get();
        
        // Pending justifications count
        $pendingJustifications = Justification::where('student_id', $student->id)
            ->where('status', Justification::STATUS_PENDING)
            ->count();
        
        // Unread notifications count
        $unreadNotifications = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();
        
        // Attendance statistics
        $attendanceStats = $this->calculateAttendanceStats($student);
        
        return view('student.dashboard.index', compact(
            'recentAbsences',
            'todaySessions',
            'pendingJustifications',
            'unreadNotifications',
            'attendanceStats'
        ));
    }

    /**
     * Calculate attendance statistics for the student
     */
    private function calculateAttendanceStats($student)
    {
        $totalAbsences = Absence::where('student_id', $student->id)->count();
        $justifiedAbsences = Absence::where('student_id', $student->id)
            ->where('status', Absence::STATUS_JUSTIFIED)
            ->count();
        
        $totalSessions = CourseSession::where('group_id', $student->group_id)->count();
        $attendanceRate = $totalSessions > 0 
            ? (($totalSessions - $totalAbsences) / $totalSessions) * 100 
            : 100;
        
        return [
            'total_absences' => $totalAbsences,
            'justified_absences' => $justifiedAbsences,
            'unjustified_absences' => $totalAbsences - $justifiedAbsences,
            'total_sessions' => $totalSessions,
            'attendance_rate' => round($attendanceRate, 2),
            'status' => $this->getAttendanceStatus($attendanceRate),
        ];
    }

    /**
     * Get attendance status based on rate
     */
    private function getAttendanceStatus($rate)
    {
        if ($rate < Setting::getAbsenceCriticalThreshold()) {
            return 'critical';
        } elseif ($rate < Setting::getAbsenceAlertThreshold()) {
            return 'alert';
        } elseif ($rate < Setting::getAbsenceWarningThreshold()) {
            return 'warning';
        } else {
            return 'good';
        }
    }
}