<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of roles and users.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $users = $query->with('college')
            ->orderBy('role')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        // Get available roles for filter dropdown
        $availableRoles = [
            'admin' => 'Administrator',
            'dean' => 'Dean',
            'overall_coordinator' => 'Overall Coordinator',
            'chairperson' => 'Chairperson',
            'faculty' => 'Faculty',
            'accreditor_lead' => 'Accreditor Lead',
            'accreditor_member' => 'Accreditor Member',
            'staff' => 'Staff'
        ];

        // Get role statistics
        $roleStats = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return view('admin.roles.index', compact(
            'users',
            'availableRoles',
            'roleStats'
        ));
    }

    /**
     * Show the form for editing a user's role.
     */
    public function edit(User $user): View
    {
        $availableRoles = [
            'admin' => 'Administrator',
            'dean' => 'Dean',
            'overall_coordinator' => 'Overall Coordinator',
            'chairperson' => 'Chairperson',
            'faculty' => 'Faculty',
            'accreditor_lead' => 'Accreditor Lead',
            'accreditor_member' => 'Accreditor Member',
            'staff' => 'Staff'
        ];

        return view('admin.roles.edit', compact('user', 'availableRoles'));
    }

    /**
     * Update the user's role.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => [
                'required',
                'string',
                Rule::in([
                    'admin',
                    'dean',
                    'overall_coordinator',
                    'chairperson',
                    'faculty',
                    'accreditor_lead',
                    'accreditor_member',
                    'staff'
                ])
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $oldRole = $user->role;
        $newRole = $request->role;

        // Update user role
        $user->update([
            'role' => $newRole,
            'permissions' => json_encode($request->permissions ?? [])
        ]);

        // Log the role change for audit purposes
        $this->activityLogger->logRoleChange(
            $user,
            'role_assigned',
            [
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'permissions' => $request->permissions ?? [],
                'changed_by' => auth()->user()->name
            ]
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "User role updated from '{$oldRole}' to '{$newRole}' successfully.");
    }

    /**
     * Bulk update roles for multiple users.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'bulk_role' => [
                'required',
                'string',
                Rule::in([
                    'admin',
                    'dean',
                    'overall_coordinator',
                    'chairperson',
                    'faculty',
                    'accreditor_lead',
                    'accreditor_member',
                    'staff'
                ])
            ]
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $updatedCount = 0;

        foreach ($users as $user) {
            $oldRole = $user->role;
            $user->update(['role' => $request->bulk_role]);

            // Log the role change
            $this->activityLogger->logRoleChange(
                $user,
                'role_assigned',
                [
                    'old_role' => $oldRole,
                    'new_role' => $request->bulk_role,
                    'bulk_update' => true,
                    'changed_by' => auth()->user()->name
                ]
            );

            $updatedCount++;
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Successfully updated roles for {$updatedCount} users.");
    }

    /**
     * Display role statistics and analytics.
     */
    public function stats(): View
    {
        $roleStats = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        $statusStats = User::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentRoleChanges = activity()
            ->where('description', 'Role updated')
            ->with('causer', 'subject')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.roles.stats', compact(
            'roleStats',
            'statusStats',
            'recentRoleChanges'
        ));
    }
}