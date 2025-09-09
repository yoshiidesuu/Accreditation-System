<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     * Redirects authenticated users to their appropriate dashboard
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userRoles = $user->getRoleNames();
            $primaryRole = $userRoles->first() ?? 'user';
            
            // If user is trying to access login/register pages while authenticated
            if ($request->routeIs('login') || $request->routeIs('register')) {
                return $this->redirectToDashboard($primaryRole);
            }
            
            // If user is accessing root path, redirect to appropriate dashboard
            if ($request->is('/')) {
                return $this->redirectToDashboard($primaryRole);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard(string $role): Response
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            
            case 'overall_coordinator':
            case 'dean':
            case 'chairperson':
            case 'faculty':
            case 'accreditor_lead':
            case 'accreditor_member':
            default:
                return redirect()->route('user.dashboard');
        }
    }
}