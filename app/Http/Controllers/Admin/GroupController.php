<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * Display a listing of groups
     */
    public function index()
    {
        $groups = Group::withCount('activeStudents')->latest()->paginate(20);
        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new group
     */
    public function create()
    {
        return view('admin.groups.create');
    }

    /**
     * Store a newly created group
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|unique:groups,code',
            'level' => 'required|string|max:50',
            'specialty' => 'required|string|max:100',
            'max_students' => 'required|integer|min:1|max:100',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Group::create([
            'name' => $request->name,
            'code' => $request->code,
            'level' => $request->level,
            'specialty' => $request->specialty,
            'max_students' => $request->max_students,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'is_active' => true,
        ]);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Groupe créé avec succès.');
    }

    /**
     * Display the specified group with its students
     */
    public function show(Group $group)
    {
        $group->load(['students.user', 'modules']);
        return view('admin.groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified group
     */
    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    /**
     * Update the specified group
     */
    public function update(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|unique:groups,code,' . $group->id,
            'level' => 'required|string|max:50',
            'specialty' => 'required|string|max:100',
            'max_students' => 'required|integer|min:1|max:100',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|integer|in:1,2',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $group->update($request->all());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Groupe mis à jour avec succès.');
    }

    /**
     * Remove (soft delete) the specified group
     */
    public function destroy(Group $group)
    {
        // Check if group has students before deleting
        if ($group->students()->exists()) {
            return back()->withErrors(['Ce groupe contient des étudiants. Veuillez les réaffecter avant de supprimer le groupe.']);
        }

        $group->update(['is_active' => false]);
        
        return back()->with('success', 'Groupe désactivé avec succès.');
    }

    /**
     * Display students in a specific group
     */
    public function students(Group $group)
    {
        $students = $group->students()->with('user')->paginate(20);
        return view('admin.groups.students', compact('group', 'students'));
    }

    /**
     * Add a student to the group
     */
    public function addStudent(Request $request, Group $group)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::find($request->student_id);
        
        // Check if student is already in a group
        if ($student->group_id) {
            return back()->withErrors(['Cet étudiant est déjà affecté à un groupe.']);
        }

        $student->update(['group_id' => $group->id]);

        return back()->with('success', 'Étudiant ajouté au groupe avec succès.');
    }

    /**
     * Remove a student from the group
     */
    public function removeStudent(Group $group, Student $student)
    {
        if ($student->group_id !== $group->id) {
            return back()->withErrors(['Cet étudiant n\'est pas dans ce groupe.']);
        }

        $student->update(['group_id' => null]);

        return back()->with('success', 'Étudiant retiré du groupe avec succès.');
    }
}