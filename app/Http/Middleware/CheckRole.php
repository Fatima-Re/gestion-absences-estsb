<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if the authenticated user has the required role(s)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  One or more roles to check
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Get authenticated user
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Veuillez vous connecter pour accéder à cette page.'
            ]);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte est désactivé. Veuillez contacter l\'administration.'
            ]);
        }
        
        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès non autorisé.');
        }
        
        return $next($request);
    }
}