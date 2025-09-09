<?php

namespace App\Policies;

use App\Models\Accreditation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccreditationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view accreditations
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Accreditation $accreditation): bool
    {
        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can view all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // Dean can view accreditations for their college
        if ($user->hasRole('dean') && $user->college_id === $accreditation->college_id) {
            return true;
        }

        // Assigned lead can view
        if ($accreditation->assigned_lead_id === $user->id) {
            return true;
        }

        // Accreditation members can view
        if ($user->hasAnyRole(['accreditor_lead', 'accreditor_member'])) {
            return $accreditation->members()->where('user_id', $user->id)->exists();
        }

        // Other roles can view accreditations for their college
        if ($user->hasAnyRole(['chairperson', 'faculty']) && $user->college_id === $accreditation->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin and overall coordinator can create accreditations
        return $user->hasAnyRole(['admin', 'overall_coordinator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Accreditation $accreditation): bool
    {
        // Admin can update all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can update all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // Assigned lead can update
        if ($accreditation->assigned_lead_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Accreditation $accreditation): bool
    {
        // Only admin can delete accreditations
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can assign accreditors.
     */
    public function assignAccreditors(User $user, Accreditation $accreditation): bool
    {
        // Admin and overall coordinator can assign accreditors
        return $user->hasAnyRole(['admin', 'overall_coordinator']);
    }

    /**
     * Determine whether the user can tag colleges.
     */
    public function tagColleges(User $user, Accreditation $accreditation): bool
    {
        // Admin and overall coordinator can tag colleges
        return $user->hasAnyRole(['admin', 'overall_coordinator']);
    }

    /**
     * Determine whether the user can evaluate the accreditation.
     */
    public function evaluate(User $user, Accreditation $accreditation): bool
    {
        // Assigned lead can evaluate
        if ($accreditation->assigned_lead_id === $user->id) {
            return true;
        }

        // Accreditation members can evaluate
        if ($user->hasAnyRole(['accreditor_lead', 'accreditor_member'])) {
            return $accreditation->members()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Accreditation $accreditation): bool
    {
        // Only admin can restore accreditations
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Accreditation $accreditation): bool
    {
        // Only admin can force delete accreditations
        return $user->hasRole('admin');
    }
}