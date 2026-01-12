<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\CourseSession;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    /**
     * Display teacher's modules
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        $modules = $teacher->modules()->with(['groups'])->paginate(20);
        
        return view('teacher.modules.index', compact('modules'));
    }

    /**
     * Display details of a specific module
     */
    public function show(Request $request, Module $module)
    {
        // Verify teacher has access to this module
        $this->authorizeTeacherModule($module);
        
        $teacher = Auth::user()->teacher;
        
        // Get date range for statistics
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());
        
        // Get sessions for this module
        $sessions = CourseSession::where('module_id', $module->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['group', 'attendanceRecords'])
            ->orderBy('date', 'desc')
            ->paginate(20);
        
        // Calculate module statistics
        $statistics = $this->calculateModuleStatistics($module, $teacher, $dateFrom, $dateTo);
        
        // Get groups assigned to this module
        $groups = $module->groups()->whereHas('courseSessions', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->get();
        
        return view('teacher.modules.show', compact('module', 'sessions', 'statistics', 'groups', 'dateFrom', 'dateTo'));
    }

    /**
     * Display attendance statistics for a module
     */
    public function statistics(Module $module, Request $request)
    {
        // Verify teacher has access to this module
        $this->authorizeTeacherModule($module);
        
        $teacher = Auth::user()->teacher;
        
        // Get date range
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());
        
        // Calculate detailed statistics
        $statistics = $this->calculateDetailedModuleStatistics($module, $teacher, $dateFrom, $dateTo);
        
        return view('teacher.modules.statistics', compact('module', 'statistics', 'dateFrom', 'dateTo'));
    }

    /**
     * Verify teacher has access to the module
     */
    private function authorizeTeacherModule(Module $module)
    {
        $teacher = Auth::user()->teacher;
        
        if (!$teacher->modules->contains($module)) {
            abort(403, 'Non autorisé.');
        }
    }

    /**
     * Calculate basic module statistics
     */
    private function calculateModuleStatistics(Module $module, $teacher, $dateFrom, $dateTo)
    {
        // Get sessions within date range
        $sessions = CourseSession::where('module_id', $module->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();
        
        $totalSessions = $sessions->count();
        $totalStudents = $module->groups->sum(function($group) {
            return $group->studentsCount();
        });
        
        // Calculate attendance rate
        $totalExpectedAttendance = $totalSessions * $totalStudents;
        $absences = Absence::whereHas('session', function($q) use ($module, $teacher, $dateFrom, $dateTo) {
                $q->where('module_id', $module->id)
                  ->where('teacher_id', $teacher->id)
                  ->whereBetween('date', [$dateFrom, $dateTo]);
            })
            ->count();
        
        $attendanceRate = $totalExpectedAttendance > 0 
            ? (($totalExpectedAttendance - $absences) / $totalExpectedAttendance) * 100 
            : 0;
        
        // Get attendance by group
        $attendanceByGroup = [];
        foreach ($module->groups as $group) {
            $groupSessions = $sessions->where('group_id', $group->id);
            $groupAbsences = Absence::whereHas('session', function($q) use ($module, $teacher, $group, $dateFrom, $dateTo) {
                    $q->where('module_id', $module->id)
                      ->where('teacher_id', $teacher->id)
                      ->where('group_id', $group->id)
                      ->whereBetween('date', [$dateFrom, $dateTo]);
                })
                ->count();
            
            $groupExpected = $groupSessions->count() * $group->studentsCount();
            $groupRate = $groupExpected > 0 ? (($groupExpected - $groupAbsences) / $groupExpected) * 100 : 0;
            
            $attendanceByGroup[] = [
                'group' => $group,
                'sessions' => $groupSessions->count(),
                'absences' => $groupAbsences,
                'attendance_rate' => round($groupRate, 2),
            ];
        }
        
        return [
            'total_sessions' => $totalSessions,
            'total_students' => $totalStudents,
            'total_absences' => $absences,
            'attendance_rate' => round($attendanceRate, 2),
            'attendance_by_group' => $attendanceByGroup,
        ];
    }

    /**
     * Calculate detailed module statistics
     */
    private function calculateDetailedModuleStatistics(Module $module, $teacher, $dateFrom, $dateTo)
    {
        $statistics = $this->calculateModuleStatistics($module, $teacher, $dateFrom, $dateTo);
        
        // Add weekly trend
        $weeklyTrend = CourseSession::where('module_id', $module->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('YEARWEEK(date, 1) as week, COUNT(*) as sessions')
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function($item) use ($module, $teacher) {
                $absences = Absence::whereHas('session', function($q) use ($module, $teacher, $item) {
                        $q->where('module_id', $module->id)
                          ->where('teacher_id', $teacher->id)
                          ->whereRaw('YEARWEEK(date, 1) = ?', [$item->week]);
                    })
                    ->count();
                
                $sessions = $item->sessions;
                $students = $module->groups->sum(function($group) {
                    return $group->studentsCount();
                });
                
                $expected = $sessions * $students;
                $rate = $expected > 0 ? (($expected - $absences) / $expected) * 100 : 0;
                
                return [
                    'week' => $item->week,
                    'sessions' => $sessions,
                    'absences' => $absences,
                    'attendance_rate' => round($rate, 2),
                ];
            });
        
        // Top absent students
        $topAbsentStudents = \App\Models\Student::whereHas('absences', function($q) use ($module, $teacher, $dateFrom, $dateTo) {
                $q->whereHas('session', function($q2) use ($module, $teacher, $dateFrom, $dateTo) {
                    $q2->where('module_id', $module->id)
                       ->where('teacher_id', $teacher->id)
                       ->whereBetween('date', [$dateFrom, $dateTo]);
                });
            })
            ->withCount(['absences' => function($q) use ($module, $teacher, $dateFrom, $dateTo) {
                $q->whereHas('session', function($q2) use ($module, $teacher, $dateFrom, $dateTo) {
                    $q2->where('module_id', $module->id)
                       ->where('teacher_id', $teacher->id)
                       ->whereBetween('date', [$dateFrom, $dateTo]);
                });
            }])
            ->orderBy('absences_count', 'desc')
            ->take(10)
            ->get();
        
        $statistics['weekly_trend'] = $weeklyTrend;
        $statistics['top_absent_students'] = $topAbsentStudents;
        
        return $statistics;
    }
}