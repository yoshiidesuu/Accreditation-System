<?php

namespace App\Policies;

use App\Models\Parameter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ParameterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view parameters
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Parameter $parameter): bool
    {
        // All authenticated users can view parameters
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create parameters
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Parameter $parameter): bool
    {
        // Only admin can update parameters
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Parameter $parameter): bool
    {
        // Only admin can delete parameters
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Parameter $parameter): bool
    {
        // Only admin can restore parameters
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Parameter $parameter): bool
    {
        // Only admin can force delete parameters
        return $user->hasRole('admin');
    }
}