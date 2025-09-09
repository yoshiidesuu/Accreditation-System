<?php

namespace App\Policies;

use App\Models\ParameterContent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ParameterContentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view parameter contents list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ParameterContent $parameterContent): bool
    {
        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can view all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // Dean can view all in their college
        if ($user->hasRole('dean') && $user->college_id === $parameterContent->college_id) {
            return true;
        }

        // Owner can always view their own content
        if ($parameterContent->uploaded_by === $user->id) {
            return true;
        }

        // Accreditors can view tagged content
        if ($user->hasAnyRole(['accreditor_lead', 'accreditor_member'])) {
            return $this->isTaggedForAccreditor($user, $parameterContent);
        }

        // Chairperson can view content from their college
        if ($user->hasRole('chairperson') && $user->college_id === $parameterContent->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, chairperson, and faculty can create parameter content
        return $user->hasAnyRole(['admin', 'chairperson', 'faculty']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ParameterContent $parameterContent): bool
    {
        // Admin can update all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner can update their own content
        if ($parameterContent->uploaded_by === $user->id) {
            return true;
        }

        // Chairperson can update content from their college
        if ($user->hasRole('chairperson') && $user->college_id === $parameterContent->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ParameterContent $parameterContent): bool
    {
        // Admin can delete all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner can delete their own content
        if ($parameterContent->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can request access to the model.
     */
    public function requestAccess(User $user, ParameterContent $parameterContent): bool
    {
        // Cannot request access to own content
        if ($parameterContent->uploaded_by === $user->id) {
            return false;
        }

        // Admin and overall coordinator don't need to request access
        if ($user->hasAnyRole(['admin', 'overall_coordinator'])) {
            return false;
        }

        // Dean can request access to content outside their college
        if ($user->hasRole('dean') && $user->college_id !== $parameterContent->college_id) {
            return true;
        }

        // Accreditors can request access to non-tagged content
        if ($user->hasAnyRole(['accreditor_lead', 'accreditor_member'])) {
            return !$this->isTaggedForAccreditor($user, $parameterContent);
        }

        // Chairperson can request access to content outside their college
        if ($user->hasRole('chairperson') && $user->college_id !== $parameterContent->college_id) {
            return true;
        }

        // Faculty can request access to content they don't own
        if ($user->hasRole('faculty')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve access requests.
     */
    public function approveAccess(User $user, ParameterContent $parameterContent): bool
    {
        // Admin can approve all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Overall coordinator can approve all
        if ($user->hasRole('overall_coordinator')) {
            return true;
        }

        // Owner can approve access to their content
        if ($parameterContent->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can download the file.
     */
    public function download(User $user, ParameterContent $parameterContent): bool
    {
        // Check if user can view the content first
        if (!$this->view($user, $parameterContent)) {
            return false;
        }

        // If content requires permission and user doesn't have it, deny download
        if ($parameterContent->requires_permission && $parameterContent->permission_status !== 'granted') {
            // Unless user is admin, overall coordinator, or owner
            return $user->hasAnyRole(['admin', 'overall_coordinator']) || 
                   $parameterContent->uploaded_by === $user->id;
        }

        return true;
    }

    /**
     * Check if content is tagged for the accreditor.
     */
    private function isTaggedForAccreditor(User $user, ParameterContent $parameterContent): bool
    {
        // Check if the parameter content is tagged for accreditation
        // and the user is assigned as an accreditor
        return $parameterContent->accreditationTags()
            ->whereHas('accreditation', function ($query) use ($user) {
                $query->where('assigned_lead_id', $user->id)
                      ->orWhereHas('members', function ($memberQuery) use ($user) {
                          $memberQuery->where('user_id', $user->id);
                      });
            })->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ParameterContent $parameterContent): bool
    {
        // Only admin can restore parameter content
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ParameterContent $parameterContent): bool
    {
        // Only admin can force delete parameter content
        return $user->hasRole('admin');
    }
}