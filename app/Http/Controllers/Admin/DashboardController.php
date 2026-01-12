<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Group;
use App\Models\Module;
use App\Models\Absence;
use App\Models\Justification;
use App\Models\CourseSession;
use Illuminate\Http\Request;    

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with statistics
     */
    public function index()
    {
        // Get counts for dashboard cards
        $stats = [
            'students_count' => Student::count(),
            'teachers_count' => Teacher::count(),
            'groups_count' => Group::active()->count(),
            'modules_count' => Module::active()->count(),
            'absences_today' => Absence::whereDate('created_at', today())->count(),
            'pending_justifications' => Justification::pending()->count(),
            'sessions_today' => CourseSession::whereDate('date', today())->count(),
        ];

        // Get recent absences for the table
        $recentAbsences = Absence::with(['student.user', 'session.module'])
            ->latest()
            ->take(10)
            ->get();

        // Get pending justifications
        $pendingJustifications = Justification::with(['student.user', 'absence.session'])
            ->pending()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentAbsences', 'pendingJustifications'));
    }
}