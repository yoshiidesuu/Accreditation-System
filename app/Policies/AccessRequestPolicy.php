<?php

namespace App\Policies;

use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccessRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view access requests (filtered by role in controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccessRequest $accessRequest): bool
    {
        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can view all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // Dean can view requests for files in their college
        if ($user->hasRole('dean') && $user->college_id === $accessRequest->file->college_id) {
            return true;
        }

        // Users can view their own requests
        if ($accessRequest->requester_id === $user->id) {
            return true;
        }

        // File owners can view requests for their files
        if ($accessRequest->file->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create access requests
        // (specific logic handled in ParameterContentPolicy::requestAccess)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccessRequest $accessRequest): bool
    {
        // Only the requester can update their own pending requests
        return $accessRequest->requester_id === $user->id && 
               $accessRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccessRequest $accessRequest): bool
    {
        // Admin can delete any request
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own pending requests
        return $accessRequest->requester_id === $user->id && 
               $accessRequest->status === 'pending';
    }

    /**
     * Determine whether the user can approve the access request.
     */
    public function approve(User $user, AccessRequest $accessRequest): bool
    {
        // Cannot approve own request
        if ($accessRequest->requester_id === $user->id) {
            return false;
        }

        // Request must be pending
        if ($accessRequest->status !== 'pending') {
            return false;
        }

        // Admin can approve all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can approve all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // File owner can approve requests for their files
        if ($accessRequest->file->uploaded_by === $user->id) {
            return true;
        }

        // Dean can approve requests for files in their college
        if ($user->hasRole('dean') && $user->college_id === $accessRequest->file->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reject the access request.
     */
    public function reject(User $user, AccessRequest $accessRequest): bool
    {
        // Same logic as approve
        return $this->approve($user, $accessRequest);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AccessRequest $accessRequest): bool
    {
        // Only admin can restore access requests
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AccessRequest $accessRequest): bool
    {
        // Only admin can force delete access requests
        return $user->hasRole('admin');
    }
}