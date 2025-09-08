<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // If no specific roles are required, allow access
        if (empty($roles)) {
            return $next($request);
        }
        
        // Check if user has any of the required roles
        $userRole = $user->role ?? 'user';
        
        if (in_array($userRole, $roles)) {
            return $next($request);
        }
        
        // Redirect based on user role if access is denied
        return $this->redirectBasedOnRole($userRole);
    }
    
    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole(string $role): Response
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have permission to access that resource.');
            
            case 'coordinator':
            case 'faculty':
            case 'accreditor_lead':
            case 'accreditor_member':
            default:
                return redirect()->route('user.dashboard')
                    ->with('error', 'You do not have permission to access that resource.');
        }
    }
}