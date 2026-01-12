<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Module;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    /**
     * Display a listing of sessions with filters
     */
    public function index(Request $request)
    {
        $query = CourseSession::with(['module', 'group', 'teacher.user']);
        
        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        
        $sessions = $query->latest()->paginate(20);
        
        // Get filter options
        $modules = Module::active()->get();
        $groups = Group::active()->get();
        $teachers = Teacher::with('user')->get();
        
        return view('admin.sessions.index', compact('sessions', 'modules', 'groups', 'teachers'));
    }

    /**
     * Show the form for creating a new session
     */
    public function create()
    {
        $modules = Module::active()->get();
        $groups = Group::active()->get();
        $teachers = Teacher::with('user')->get();
        
        return view('admin.sessions.create', compact('modules', 'groups', 'teachers'));
    }

    /**
     * Store a newly created session
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:50',
            'topic' => 'nullable|string|max:200',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check for scheduling conflicts
        $conflict = $this->checkSchedulingConflict($request);
        
        if ($conflict['has_conflict']) {
            return back()->withErrors([
                $conflict['type'] => $conflict['message'],
            ])->withInput();
        }

        // Create the session
        CourseSession::create([
            'module_id' => $request->module_id,
            'group_id' => $request->group_id,
            'teacher_id' => $request->teacher_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
            'topic' => $request->topic,
            'description' => $request->description,
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Séance planifiée avec succès.');
    }

    /**
     * Display the specified session
     */
    public function show(CourseSession $session)
    {
        $session->load(['module', 'group', 'teacher.user', 'attendanceRecords.student.user']);
        return view('admin.sessions.show', compact('session'));
    }

    /**
     * Show the form for editing the specified session
     */
    public function edit(CourseSession $session)
    {
        $modules = Module::active()->get();
        $groups = Group::active()->get();
        $teachers = Teacher::with('user')->get();
        
        return view('admin.sessions.edit', compact('session', 'modules', 'groups', 'teachers'));
    }

    /**
     * Update the specified session
     */
    public function update(Request $request, CourseSession $session)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:50',
            'topic' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check for scheduling conflicts (excluding current session)
        $conflict = $this->checkSchedulingConflict($request, $session->id);
        
        if ($conflict['has_conflict']) {
            return back()->withErrors([
                $conflict['type'] => $conflict['message'],
            ])->withInput();
        }

        // Update the session
        $session->update($request->all());

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Séance mise à jour avec succès.');
    }

    /**
     * Remove the specified session
     */
    public function destroy(CourseSession $session)
    {
        // Check if session has attendance records
        if ($session->attendanceRecords()->exists()) {
            return back()->withErrors([
                'session' => 'Impossible de supprimer une séance avec des présences enregistrées.'
            ]);
        }

        $session->delete();

        return back()->with('success', 'Séance supprimée avec succès.');
    }

    /**
     * Cancel a session
     */
    public function cancel(Request $request, CourseSession $session)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $session->update([
            'status' => CourseSession::STATUS_CANCELLED,
            'is_cancelled' => true,
            'cancellation_reason' => $request->reason,
        ]);

        // Notify teacher and students
        $this->notifySessionCancellation($session, $request->reason);

        return back()->with('success', 'Séance annulée avec succès.');
    }

    /**
     * Check for scheduling conflicts
     */
    private function checkSchedulingConflict($request, $excludeSessionId = null)
    {
        // Check room conflict
        $roomQuery = CourseSession::where('date', $request->date)
            ->where('room', $request->room)
            ->where('id', '!=', $excludeSessionId)
            ->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('start_time', '<=', $request->start_time)
                       ->where('end_time', '>', $request->start_time);
                })->orWhere(function($q2) use ($request) {
                    $q2->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>=', $request->end_time);
                });
            });
        
        if ($roomQuery->exists()) {
            return [
                'has_conflict' => true,
                'type' => 'room',
                'message' => 'La salle est déjà occupée à cette heure.',
            ];
        }

        // Check teacher conflict
        $teacherQuery = CourseSession::where('date', $request->date)
            ->where('teacher_id', $request->teacher_id)
            ->where('id', '!=', $excludeSessionId)
            ->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('start_time', '<=', $request->start_time)
                       ->where('end_time', '>', $request->start_time);
                })->orWhere(function($q2) use ($request) {
                    $q2->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>=', $request->end_time);
                });
            });
        
        if ($teacherQuery->exists()) {
            return [
                'has_conflict' => true,
                'type' => 'teacher',
                'message' => "L'enseignant a déjà une séance à cette heure.",
            ];
        }

        // Check group conflict
        $groupQuery = CourseSession::where('date', $request->date)
            ->where('group_id', $request->group_id)
            ->where('id', '!=', $excludeSessionId)
            ->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('start_time', '<=', $request->start_time)
                       ->where('end_time', '>', $request->start_time);
                })->orWhere(function($q2) use ($request) {
                    $q2->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>=', $request->end_time);
                });
            });
        
        if ($groupQuery->exists()) {
            return [
                'has_conflict' => true,
                'type' => 'group',
                'message' => 'Le groupe a déjà une séance à cette heure.',
            ];
        }

        return ['has_conflict' => false];
    }

    /**
     * Notify about session cancellation
     */
    private function notifySessionCancellation($session, $reason)
    {
        // Notify teacher
        \App\Models\Notification::create([
            'user_id' => $session->teacher->user_id,
            'title' => 'Séance annulée',
            'message' => 'Votre séance du ' . $session->date->format('d/m/Y') . ' a été annulée. Raison: ' . $reason,
            'type' => \App\Models\Notification::TYPE_INFO,
            'related_model' => 'CourseSession',
            'related_id' => $session->id,
        ]);

        // Notify students
        $students = $session->group->students;
        foreach ($students as $student) {
            \App\Models\Notification::create([
                'user_id' => $student->user_id,
                'title' => 'Séance annulée',
                'message' => 'La séance du ' . $session->date->format('d/m/Y') . ' (' . $session->module->name . ') a été annulée.',
                'type' => \App\Models\Notification::TYPE_INFO,
                'related_model' => 'CourseSession',
                'related_id' => $session->id,
            ]);
        }
    }
}