<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\CourseSession;
use App\Models\Student;
use App\Models\Module;
use App\Models\Group;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StatisticsExport;

class StatisticController extends Controller
{
    /**
     * Display attendance statistics with filters
     */
    public function index(Request $request)
    {
        // Get date range
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());
        
        // Calculate statistics
        $statistics = $this->calculateStatistics($dateFrom, $dateTo, $request);
        
        // Get filter options
        $modules = Module::active()->get();
        $groups = Group::active()->get();
        
        return view('admin.statistics.index', compact('statistics', 'dateFrom', 'dateTo', 'modules', 'groups'));
    }

    /**
     * Export statistics to PDF or Excel
     */
    public function export(Request $request)
    {
        // Get date range
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());
        
        // Calculate statistics
        $statistics = $this->calculateStatistics($dateFrom, $dateTo, $request);
        $format = $request->get('format', 'excel');
        
        // Generate report based on format
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.statistics.export.pdf', compact('statistics'));
            return $pdf->download('statistiques-' . date('Y-m-d') . '.pdf');
        }
        
        // Generate Excel report
        return Excel::download(new StatisticsExport($statistics), 'statistiques-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Calculate comprehensive statistics
     */
    private function calculateStatistics($dateFrom, $dateTo, $request = null)
    {
        // Base queries with date filter
        $baseSessionQuery = CourseSession::whereBetween('date', [$dateFrom, $dateTo]);
        $baseAbsenceQuery = Absence::whereBetween('created_at', [$dateFrom, $dateTo]);
        
        // Apply additional filters
        if ($request && $request->filled('module_id')) {
            $moduleId = $request->module_id;
            $baseSessionQuery->where('module_id', $moduleId);
            $baseAbsenceQuery->whereHas('session', function($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            });
        }
        
        if ($request && $request->filled('group_id')) {
            $groupId = $request->group_id;
            $baseSessionQuery->where('group_id', $groupId);
            $baseAbsenceQuery->whereHas('session', function($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }
        
        // Overall statistics
        $totalSessions = $baseSessionQuery->count();
        $totalAbsences = $baseAbsenceQuery->count();
        $totalStudents = Student::count();
        
        $totalExpectedAttendance = $totalSessions * $totalStudents;
        $overallAttendanceRate = $totalExpectedAttendance > 0 
            ? (($totalExpectedAttendance - $totalAbsences) / $totalExpectedAttendance) * 100 
            : 0;
        
        // Module-wise statistics
        $moduleStats = Module::active()->get()->map(function($module) use ($dateFrom, $dateTo) {
            $moduleSessions = CourseSession::where('module_id', $module->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count();
            
            $moduleAbsences = Absence::whereHas('session', function($q) use ($module, $dateFrom, $dateTo) {
                    $q->where('module_id', $module->id)
                      ->whereBetween('date', [$dateFrom, $dateTo]);
                })
                ->count();
            
            $moduleStudents = $module->groups->sum(function($group) {
                return $group->studentsCount();
            });
            
            $expectedAttendance = $moduleSessions * $moduleStudents;
            $attendanceRate = $expectedAttendance > 0 
                ? (($expectedAttendance - $moduleAbsences) / $expectedAttendance) * 100 
                : 0;
            
            return [
                'module' => $module,
                'sessions' => $moduleSessions,
                'absences' => $moduleAbsences,
                'students' => $moduleStudents,
                'attendance_rate' => round($attendanceRate, 2),
            ];
        })->sortByDesc('absences');
        
        // Group-wise statistics
        $groupStats = Group::active()->get()->map(function($group) use ($dateFrom, $dateTo) {
            $groupSessions = CourseSession::where('group_id', $group->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count();
            
            $groupAbsences = Absence::whereHas('session', function($q) use ($group, $dateFrom, $dateTo) {
                    $q->where('group_id', $group->id)
                      ->whereBetween('date', [$dateFrom, $dateTo]);
                })
                ->count();
            
            $groupStudents = $group->studentsCount();
            $expectedAttendance = $groupSessions * $groupStudents;
            $attendanceRate = $expectedAttendance > 0 
                ? (($expectedAttendance - $groupAbsences) / $expectedAttendance) * 100 
                : 0;
            
            return [
                'group' => $group,
                'sessions' => $groupSessions,
                'absences' => $groupAbsences,
                'students' => $groupStudents,
                'attendance_rate' => round($attendanceRate, 2),
            ];
        })->sortByDesc('absences');
        
        // Top absent students
        $topAbsentStudents = Student::with(['user', 'group'])
            ->withCount(['absences' => function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->orderBy('absences_count', 'desc')
            ->take(20)
            ->get();
        
        // Daily trend
        $dailyTrend = CourseSession::whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('date, COUNT(*) as sessions')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) use ($dateFrom, $dateTo) {
                $absences = Absence::whereHas('session', function($q) use ($item) {
                        $q->where('date', $item->date);
                    })
                    ->count();
                
                $sessions = $item->sessions;
                $students = Student::count();
                $expected = $sessions * $students;
                $rate = $expected > 0 ? (($expected - $absences) / $expected) * 100 : 0;
                
                return [
                    'date' => $item->date,
                    'sessions' => $sessions,
                    'absences' => $absences,
                    'attendance_rate' => round($rate, 2),
                ];
            });
        
        // Weekly trend
        $weeklyTrend = CourseSession::whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('YEARWEEK(date, 1) as week, COUNT(*) as sessions')
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function($item) use ($dateFrom, $dateTo) {
                $absences = Absence::whereHas('session', function($q) use ($item) {
                        $q->whereRaw('YEARWEEK(date, 1) = ?', [$item->week]);
                    })
                    ->count();
                
                $sessions = $item->sessions;
                $students = Student::count();
                $expected = $sessions * $students;
                $rate = $expected > 0 ? (($expected - $absences) / $expected) * 100 : 0;
                
                return [
                    'week' => $item->week,
                    'sessions' => $sessions,
                    'absences' => $absences,
                    'attendance_rate' => round($rate, 2),
                ];
            });
        
        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'overall' => [
                'sessions' => $totalSessions,
                'absences' => $totalAbsences,
                'students' => $totalStudents,
                'attendance_rate' => round($overallAttendanceRate, 2),
            ],
            'module_stats' => $moduleStats,
            'group_stats' => $groupStats,
            'top_absent_students' => $topAbsentStudents,
            'daily_trend' => $dailyTrend,
            'weekly_trend' => $weeklyTrend,
        ];
    }
}