<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Module;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AbsencesExport;


class AbsenceController extends Controller
{
    /**
     * Display a listing of absences with filters
     */
    public function index(Request $request)
    {
        // Build query with relationships
        $query = Absence::with(['student.user', 'session.module', 'justification']);
        
        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->filled('module_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('module_id', $request->module_id);
            });
        }
        
        if ($request->filled('group_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->whereBetween('date', [$request->date_from, $request->date_to]);
            });
        }
        
        $absences = $query->latest()->paginate(20);
        
        // Get filter data for dropdowns
        $modules = Module::active()->get();
        $groups = Group::active()->get();
        $students = Student::with('user')->get();
        
        return view('admin.absences.index', compact('absences', 'modules', 'groups', 'students'));
    }

    /**
     * Export absences to PDF or Excel
     */
    public function export(Request $request)
    {
        // Build query with filters (same as index)
        $query = Absence::with(['student.user', 'session.module', 'justification']);
        
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->filled('module_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('module_id', $request->module_id);
            });
        }
        
        if ($request->filled('group_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->whereBetween('date', [$request->date_from, $request->date_to]);
            });
        }
        
        $absences = $query->get();
        $format = $request->get('format', 'excel');
        
        // Generate PDF
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.absences.export.pdf', compact('absences'));
            return $pdf->download('absences-' . date('Y-m-d') . '.pdf');
        }
        
        // Generate Excel
        return Excel::download(new AbsencesExport($absences), 'absences-' . date('Y-m-d') . '.xlsx');
    }
}