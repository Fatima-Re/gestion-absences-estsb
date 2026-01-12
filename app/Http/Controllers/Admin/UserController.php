<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users with optional filters
     */
    public function index(Request $request)
    {
        // Build query with filters
        $query = User::query();
        
        // Filter by role if specified
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by status if specified
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        
        $users = $query->latest()->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $groups = Group::active()->get();
        return view('admin.users.create', compact('groups'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,teacher,student',
            'phone' => 'nullable|string|max:20',
        ]);
        
        // Add role-specific validation
        if ($request->role === 'student') {
            $validator->addRules([
                'student_number' => 'required|string|unique:students,student_number',
                'group_id' => 'required|exists:groups,id',
            ]);
        } elseif ($request->role === 'teacher') {
            $validator->addRules([
                'teacher_code' => 'required|string|unique:teachers,teacher_code',
                'specialization' => 'required|string|max:100',
            ]);
        }
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Generate random password
        $password = $this->generateRandomPassword();
        
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => true,
        ]);
        
        // Create role-specific profile
        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'student_number' => $request->student_number,
                'group_id' => $request->group_id,
            ]);
        } elseif ($request->role === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
                'teacher_code' => $request->teacher_code,
                'specialization' => $request->specialization,
            ]);
        }
        
        // TODO: Send welcome email with credentials
        
        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur créé avec succès. Mot de passe temporaire: $password");
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['student.group', 'teacher']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $groups = Group::active()->get();
        return view('admin.users.edit', compact('user', 'groups'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        
        // Add role-specific validation
        if ($user->role === 'student' && $user->student) {
            $validator->addRules([
                'student_number' => 'required|string|unique:students,student_number,' . $user->student->id,
                'group_id' => 'required|exists:groups,id',
            ]);
        } elseif ($user->role === 'teacher' && $user->teacher) {
            $validator->addRules([
                'teacher_code' => 'required|string|unique:teachers,teacher_code,' . $user->teacher->id,
                'specialization' => 'required|string|max:100',
            ]);
        }
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        
        // Update role-specific profile
        if ($user->role === 'student' && $user->student) {
            $user->student->update([
                'student_number' => $request->student_number,
                'group_id' => $request->group_id,
            ]);
        } elseif ($user->role === 'teacher' && $user->teacher) {
            $user->teacher->update([
                'teacher_code' => $request->teacher_code,
                'specialization' => $request->specialization,
            ]);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Remove (soft delete) the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->withErrors(['Vous ne pouvez pas supprimer votre propre compte.']);
        }
        
        // Soft delete by deactivating
        $user->update(['is_active' => false]);
        
        return back()->with('success', 'Utilisateur désactivé avec succès.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $password = $this->generateRandomPassword();
        $user->update(['password' => Hash::make($password)]);
        
        // TODO: Send email with new password
        
        return back()->with('success', "Mot de passe réinitialisé. Nouveau mot de passe: $password");
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->withErrors(['Vous ne pouvez pas désactiver votre propre compte.']);
        }
        
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Utilisateur $status avec succès.");
    }

    /**
     * Show import form for bulk user creation
     */
    public function showImportForm()
    {
        return view('admin.users.import');
    }

    /**
     * Import users from CSV/Excel file
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
            'group_id' => 'required|exists:groups,id',
        ]);
        
        // TODO: Implement CSV/Excel import logic
        
        return back()->with('success', 'Importation des étudiants en cours...');
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
}