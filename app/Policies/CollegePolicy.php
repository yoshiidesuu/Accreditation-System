<?php

namespace App\Policies;

use App\Models\College;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CollegePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view colleges
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, College $college): bool
    {
        // All authenticated users can view colleges
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin and overall_coordinator can create colleges
        return $user->hasAnyRole(['admin', 'overall_coordinator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, College $college): bool
    {
        // Admin can update any college
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can update colleges
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, College $college): bool
    {
        // Only admin can delete colleges
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, College $college): bool
    {
        // Only admin can restore colleges
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, College $college): bool
    {
        // Only admin can force delete colleges
        return $user->hasRole('admin');
    }
}