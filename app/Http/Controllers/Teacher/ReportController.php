<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\CourseSession;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class ReportController extends Controller
{
    /**
     * Display report generation page
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        $modules = $teacher->modules;
        
        return view('teacher.reports.index', compact('modules'));
    }

    /**
     * Generate a report based on criteria
     */
    public function generate(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        // Validate report parameters
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel',
            'include_comments' => 'boolean',
            'include_graphs' => 'boolean',
            'group_id' => 'nullable|exists:groups,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $module = Module::findOrFail($request->module_id);
        
        // Verify teacher has access to this module
        if (!$teacher->modules->contains($module)) {
            abort(403, 'Non autorisé.');
        }
        
        // Build query for sessions
        $query = CourseSession::where('module_id', $module->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$request->date_from, $request->date_to]);
        
        // Filter by group if specified
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        
        $sessions = $query->with(['group', 'attendanceRecords.student.user'])->get();
        
        // Calculate statistics
        $statistics = $this->calculateReportStatistics($module, $teacher, $sessions, $request->all());
        
        // Generate report based on format
        if ($request->format === 'pdf') {
            return $this->generatePdfReport($module, $teacher, $statistics, $request->all());
        }
        
        // Generate Excel report
        return $this->generateExcelReport($module, $teacher, $statistics, $request->all());
    }

    /**
     * Display statistics for a specific module
     */
    public function statistics(Module $module, Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        // Verify teacher has access to this module
        if (!$teacher->modules->contains($module)) {
            abort(403, 'Non autorisé.');
        }
        
        // Get date range
        $dateFrom = $request->get('date_from', now()->subMonth());
        $dateTo = $request->get('date_to', now());
        
        // Get sessions
        $sessions = CourseSession::where('module_id', $module->id)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['group', 'attendanceRecords'])
            ->get();
        
        // Calculate statistics
        $statistics = $this->calculateReportStatistics($module, $teacher, $sessions, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        
        return view('teacher.reports.statistics', compact('module', 'statistics', 'dateFrom', 'dateTo'));
    }

    /**
     * Calculate statistics for the report
     */
    private function calculateReportStatistics($module, $teacher, $sessions, $params)
    {
        $totalSessions = $sessions->count();
        $totalStudents = $module->groups->sum(function($group) {
            return $group->studentsCount();
        });
        
        // Group sessions by group
        $sessionsByGroup = $sessions->groupBy('group_id');
        
        // Calculate statistics by group
        $groupStatistics = [];
        foreach ($sessionsByGroup as $groupId => $groupSessions) {
            $group = $groupSessions->first()->group;
            $groupStudents = $group->studentsCount();
            
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            
            foreach ($groupSessions as $session) {
                $presentCount += $session->attendanceRecords->where('status', 'present')->count();
                $absentCount += $session->attendanceRecords->where('status', 'absent')->count();
                $lateCount += $session->attendanceRecords->where('status', 'late')->count();
            }
            
            $expectedAttendance = $groupSessions->count() * $groupStudents;
            $attendanceRate = $expectedAttendance > 0 ? ($presentCount / $expectedAttendance) * 100 : 0;
            
            $groupStatistics[] = [
                'group' => $group,
                'sessions' => $groupSessions->count(),
                'students' => $groupStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'attendance_rate' => round($attendanceRate, 2),
            ];
        }
        
        // Calculate overall statistics
        $totalPresent = collect($groupStatistics)->sum('present');
        $totalAbsent = collect($groupStatistics)->sum('absent');
        $totalLate = collect($groupStatistics)->sum('late');
        $totalExpected = $totalSessions * $totalStudents;
        $overallRate = $totalExpected > 0 ? ($totalPresent / $totalExpected) * 100 : 0;
        
        // Get top absent students
        $topAbsentStudents = \App\Models\Student::whereHas('absences', function($q) use ($module, $teacher, $params) {
                $q->whereHas('session', function($q2) use ($module, $teacher, $params) {
                    $q2->where('module_id', $module->id)
                       ->where('teacher_id', $teacher->id)
                       ->whereBetween('date', [$params['date_from'], $params['date_to']]);
                });
            })
            ->withCount(['absences' => function($q) use ($module, $teacher, $params) {
                $q->whereHas('session', function($q2) use ($module, $teacher, $params) {
                    $q2->where('module_id', $module->id)
                       ->where('teacher_id', $teacher->id)
                       ->whereBetween('date', [$params['date_from'], $params['date_to']]);
                });
            }])
            ->orderBy('absences_count', 'desc')
            ->take(10)
            ->get();
        
        // Daily attendance trend
        $dailyTrend = [];
        $currentDate = \Carbon\Carbon::parse($params['date_from']);
        $endDate = \Carbon\Carbon::parse($params['date_to']);
        
        while ($currentDate <= $endDate) {
            $dateSessions = $sessions->where('date', $currentDate->format('Y-m-d'));
            $datePresent = 0;
            $dateAbsent = 0;
            
            foreach ($dateSessions as $session) {
                $datePresent += $session->attendanceRecords->where('status', 'present')->count();
                $dateAbsent += $session->attendanceRecords->where('status', 'absent')->count();
            }
            
            $dailyTrend[] = [
                'date' => $currentDate->format('Y-m-d'),
                'sessions' => $dateSessions->count(),
                'present' => $datePresent,
                'absent' => $dateAbsent,
                'attendance_rate' => ($datePresent + $dateAbsent) > 0 
                    ? ($datePresent / ($datePresent + $dateAbsent)) * 100 
                    : 0,
            ];
            
            $currentDate->addDay();
        }
        
        return [
            'module' => $module,
            'teacher' => $teacher,
            'period' => [
                'from' => $params['date_from'],
                'to' => $params['date_to'],
            ],
            'total_sessions' => $totalSessions,
            'total_students' => $totalStudents,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_late' => $totalLate,
            'overall_attendance_rate' => round($overallRate, 2),
            'group_statistics' => $groupStatistics,
            'top_absent_students' => $topAbsentStudents,
            'daily_trend' => $dailyTrend,
        ];
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($module, $teacher, $statistics, $params)
    {
        $pdf = Pdf::loadView('teacher.reports.pdf', [
            'statistics' => $statistics,
            'include_comments' => $params['include_comments'] ?? false,
            'include_graphs' => $params['include_graphs'] ?? false,
        ]);
        
        $filename = 'rapport-' . $module->code . '-' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($module, $teacher, $statistics, $params)
    {
        $filename = 'rapport-' . $module->code . '-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new AttendanceExport($statistics, $filename), $filename);
    }
}