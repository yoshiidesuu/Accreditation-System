<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CanRequestAccessMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware allows users to either access content directly (if they have permission)
     * or request access to content (if they have request permission)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resource, string $action = 'view'): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $directPermission = "{$resource}.{$action}";
        $requestPermission = "{$resource}.request_access";
        
        // Check if user has direct permission to the resource
        if ($user->can($directPermission)) {
            return $next($request);
        }
        
        // Check if user can request access to the resource
        if ($user->can($requestPermission)) {
            // Add a flag to the request indicating this is request-access mode
            $request->attributes->set('can_request_access', true);
            $request->attributes->set('requested_resource', $resource);
            $request->attributes->set('requested_action', $action);
            
            return $next($request);
        }
        
        // User has neither direct access nor request access
        $userRoles = $user->getRoleNames();
        $primaryRole = $userRoles->first() ?? 'user';
        
        return $this->redirectBasedOnRole($primaryRole);
    }
    
    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole(string $role): Response
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have permission to access this resource.');
            
            case 'overall_coordinator':
            case 'dean':
            case 'chairperson':
            case 'faculty':
            case 'accreditor_lead':
            case 'accreditor_member':
            default:
                return redirect()->route('user.dashboard')
                    ->with('error', 'You do not have permission to access this resource.');
        }
    }
}
