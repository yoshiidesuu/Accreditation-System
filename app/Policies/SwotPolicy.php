<?php

namespace App\Policies;

use App\Models\SwotEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SwotPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['chairperson', 'faculty', 'accreditor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SwotEntry $swotEntry): bool
    {
        // Accreditors can view all SWOT entries
        if ($user->hasRole('accreditor')) {
            return true;
        }

        // Chairpersons and faculty can view their own entries
        if ($user->hasRole(['chairperson', 'faculty'])) {
            return $swotEntry->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['chairperson', 'faculty']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SwotEntry $swotEntry): bool
    {
        // Only the creator can update, and only if not yet approved
        if ($user->hasRole(['chairperson', 'faculty']) && 
            $swotEntry->created_by === $user->id) {
            return $swotEntry->status !== SwotEntry::STATUS_APPROVED;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SwotEntry $swotEntry): bool
    {
        // Only the creator can delete, and only if not yet approved
        if ($user->hasRole(['chairperson', 'faculty']) && 
            $swotEntry->created_by === $user->id) {
            return $swotEntry->status !== SwotEntry::STATUS_APPROVED;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SwotEntry $swotEntry): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SwotEntry $swotEntry): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can review/approve the model.
     */
    public function review(User $user, SwotEntry $swotEntry): bool
    {
        return $user->hasRole('accreditor') && 
               $swotEntry->status === SwotEntry::STATUS_PENDING;
    }
}
