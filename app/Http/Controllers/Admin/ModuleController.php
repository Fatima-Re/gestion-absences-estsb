<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Teacher;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules
     */
    public function index()
    {
        $modules = Module::with(['teachers', 'groups'])->latest()->paginate(20);
        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module
     */
    public function create()
    {
        return view('admin.modules.create');
    }

    /**
     * Store a newly created module
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:modules,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1',
            'hours' => 'required|integer|min:1',
            'semester' => 'required|integer|in:1,2',
            'academic_year' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Module::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'credits' => $request->credits,
            'hours' => $request->hours,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
            'is_active' => true,
        ]);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module créé avec succès.');
    }

    /**
     * Display the specified module with details
     */
    public function show(Module $module)
    {
        $module->load(['teachers.user', 'groups', 'courseSessions']);
        return view('admin.modules.show', compact('module'));
    }

    /**
     * Show the form for editing the specified module
     */
    public function edit(Module $module)
    {
        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Update the specified module
     */
    public function update(Request $request, Module $module)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:modules,code,' . $module->id,
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1',
            'hours' => 'required|integer|min:1',
            'semester' => 'required|integer|in:1,2',
            'academic_year' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $module->update($request->all());

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module mis à jour avec succès.');
    }

    /**
     * Remove (soft delete) the specified module
     */
    public function destroy(Module $module)
    {
        // Check if module has sessions before deleting
        if ($module->courseSessions()->exists()) {
            return back()->withErrors(['Ce module a des séances planifiées. Veuillez les supprimer avant de désactiver le module.']);
        }

        $module->update(['is_active' => false]);
        
        return back()->with('success', 'Module désactivé avec succès.');
    }

    /**
     * Assign a teacher to the module
     */
    public function assignTeacher(Request $request, Module $module)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'is_responsible' => 'boolean',
        ]);

        // Check if teacher is already assigned
        if ($module->teachers()->where('teacher_id', $request->teacher_id)->exists()) {
            return back()->withErrors(['Cet enseignant est déjà assigné à ce module.']);
        }

        $module->teachers()->attach($request->teacher_id, [
            'is_responsible' => $request->boolean('is_responsible'),
            'academic_year' => $module->academic_year,
            'semester' => $module->semester,
        ]);

        return back()->with('success', 'Enseignant assigné au module avec succès.');
    }

    /**
     * Assign a group to the module
     */
    public function assignGroup(Request $request, Module $module)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        // Check if group is already assigned
        if ($module->groups()->where('group_id', $request->group_id)->exists()) {
            return back()->withErrors(['Ce groupe est déjà assigné à ce module.']);
        }

        $module->groups()->attach($request->group_id, [
            'academic_year' => $module->academic_year,
            'semester' => $module->semester,
        ]);

        return back()->with('success', 'Module assigné au groupe avec succès.');
    }

    /**
     * Remove a teacher from the module
     */
    public function removeTeacher(Module $module, Teacher $teacher)
    {
        // Check if teacher is assigned to the module
        if (!$module->teachers()->where('teacher_id', $teacher->id)->exists()) {
            return back()->withErrors(['Cet enseignant n\'est pas assigné à ce module.']);
        }

        // Check if teacher has upcoming sessions
        $upcomingSessions = $module->courseSessions()
            ->where('teacher_id', $teacher->id)
            ->where('date', '>=', now())
            ->exists();

        if ($upcomingSessions) {
            return back()->withErrors(['Cet enseignant a des séances planifiées pour ce module.']);
        }

        $module->teachers()->detach($teacher->id);

        return back()->with('success', 'Enseignant retiré du module avec succès.');
    }

    /**
     * Remove a group from the module
     */
    public function removeGroup(Module $module, Group $group)
    {
        // Check if group is assigned to the module
        if (!$module->groups()->where('group_id', $group->id)->exists()) {
            return back()->withErrors(['Ce groupe n\'est pas assigné à ce module.']);
        }

        // Check if group has sessions for this module
        $hasSessions = $module->courseSessions()
            ->where('group_id', $group->id)
            ->exists();

        if ($hasSessions) {
            return back()->withErrors(['Ce groupe a des séances planifiées pour ce module.']);
        }

        $module->groups()->detach($group->id);

        return back()->with('success', 'Groupe retiré du module avec succès.');
    }

    /**
     * Display teachers assigned to the module
     */
    public function teachers(Module $module)
    {
        $teachers = $module->teachers()->with('user')->paginate(20);
        $availableTeachers = Teacher::whereDoesntHave('modules', function($query) use ($module) {
            $query->where('module_id', $module->id);
        })->with('user')->get();
        
        return view('admin.modules.teachers', compact('module', 'teachers', 'availableTeachers'));
    }

    /**
     * Display groups assigned to the module
     */
    public function groups(Module $module)
    {
        $groups = $module->groups()->paginate(20);
        $availableGroups = Group::whereDoesntHave('modules', function($query) use ($module) {
            $query->where('module_id', $module->id);
        })->get();
        
        return view('admin.modules.groups', compact('module', 'groups', 'availableGroups'));
    }
}