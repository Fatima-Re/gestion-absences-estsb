<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    /**
     * Display login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Attempt authentication
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Role-based redirection
            return $this->redirectBasedOnRole($user);
        }
        
        // Authentication failed
        return back()->withErrors([
            'email' => 'Les identifiants sont incorrects.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Update user profile (common for all roles)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
            $user->password = Hash::make($request->new_password);
        }
        
        $user->save();
        
        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $user = User::where('email', $request->email)->first();
        
        // Generate reset token
        $token = Str::random(64);
        
        // Store reset token in database
        DB::table('password_resets')->where('email', $user->email)->delete();
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);
        
        // Generate reset link
        $resetLink = route('password.reset', ['token' => $token]);
        
        // Send email
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($resetLink, $user->name));
            return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre email.');
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.']);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Find the reset record
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();
        
        // Validate token exists and is not expired (60 minutes)
        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['token' => 'Le lien de réinitialisation est invalide.']);
        }
        
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Le lien de réinitialisation a expiré. Veuillez demander un nouveau lien.']);
        }
        
        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        
        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();
        
        return redirect()->route('login')->with('status', 'Mot de passe réinitialisé avec succès. Veuillez vous connecter avec votre nouveau mot de passe.');
    }

    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole(User $user)
    {
        switch ($user->role) {
            case User::ROLE_ADMIN:
                return redirect()->route('admin.dashboard');
            case User::ROLE_TEACHER:
                return redirect()->route('teacher.dashboard');
            case User::ROLE_STUDENT:
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
}