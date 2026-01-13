<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Module;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsenceController extends Controller
{
    /**
     * Display student's absences with filters
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        
        $query = Absence::where('student_id', $student->id)
            ->with(['session.module', 'justification']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('module_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('module_id', $request->module_id);
            });
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->whereBetween('date', [$request->date_from, $request->date_to]);
            });
        }
        
        $absences = $query->latest()->paginate(20);
        $modules = $student->group->modules;
        
        return view('student.absences.index', compact('absences', 'modules'));
    }

    /**
     * Display attendance statistics by module
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        $modules = $student->group->modules;
        
        $statistics = [];
        
        foreach ($modules as $module) {
            $moduleAbsences = Absence::where('student_id', $student->id)
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
            
            $justifiedAbsences = Absence::where('student_id', $student->id)
                ->whereHas('session', function($q) use ($module) {
                    $q->where('module_id', $module->id);
                })
                ->where('status', Absence::STATUS_JUSTIFIED)
                ->count();
            
            $statistics[] = [
                'module' => $module,
                'absences' => $moduleAbsences,
                'justified_absences' => $justifiedAbsences,
                'unjustified_absences' => $moduleAbsences - $justifiedAbsences,
                'total_sessions' => $moduleSessions,
                'attendance_rate' => round($attendanceRate, 2),
            ];
        }
        
        return view('student.absences.statistics', compact('statistics'));
    }

    /**
     * Generate and download absence report as PDF
     */
    public function report(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        
        $user = $request->user();
        $student = $user->student;
        
        $dateFrom = $request->date_from ?: now()->subYear();
        $dateTo = $request->date_to ?: now();
        
        // Get absences within date range
        $absences = Absence::where('student_id', $student->id)
            ->whereHas('session', function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('date', [$dateFrom, $dateTo]);
            })
            ->with(['session.module', 'justification'])
            ->get();
        
        // Calculate statistics
        $totalAbsences = $absences->count();
        $justifiedAbsences = $absences->where('status', Absence::STATUS_JUSTIFIED)->count();
        
        $totalSessions = \App\Models\CourseSession::where('group_id', $student->group_id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->count();
        
        $attendanceRate = $totalSessions > 0 
            ? (($totalSessions - $totalAbsences) / $totalSessions) * 100 
            : 100;
        
        // Generate PDF
        $pdf = Pdf::loadView('student.absences.report', [
            'student' => $student,
            'absences' => $absences,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_absences' => $totalAbsences,
            'justified_absences' => $justifiedAbsences,
            'attendance_rate' => round($attendanceRate, 2),
        ]);
        
        return $pdf->download('releve-absences-' . date('Y-m-d') . '.pdf');
    }
}