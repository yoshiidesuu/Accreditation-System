<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

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
        
        // Check if user has any of the required roles using spatie/laravel-permission
        if (!$user->hasAnyRole($roles)) {
            // Get user's primary role for redirection
            $userRoles = $user->getRoleNames();
            $primaryRole = $userRoles->first() ?? 'user';
            
            return $this->redirectBasedOnRole($primaryRole);
        }
        
        return $next($request);
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
            
            case 'overall_coordinator':
            case 'dean':
            case 'chairperson':
            case 'faculty':
            case 'accreditor_lead':
            case 'accreditor_member':
            default:
                return redirect()->route('user.dashboard')
                    ->with('error', 'You do not have permission to access that resource.');
        }
    }
}