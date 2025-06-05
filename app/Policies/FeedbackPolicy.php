<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FeedbackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('department_head') || $user->hasRole('department_staff');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Feedback $feedback): bool
    {
        // Department heads can view feedback for bookings in their department
        if ($user->hasRole('department_head') && $feedback->booking && $feedback->booking->department_id === $user->department_id) {
            return true;
        }
        
        // Users can view their own feedback
        return $user->id === $feedback->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('department_head') || $user->hasRole('department_staff');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Feedback $feedback): bool
    {
        // Department heads can update feedback for bookings in their department
        if ($user->hasRole('department_head') && $feedback->booking && $feedback->booking->department_id === $user->department_id) {
            return !$feedback->is_approved;
        }
        
        // Users can update their own feedback if it's not approved
        return $user->id === $feedback->user_id && !$feedback->is_approved;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Feedback $feedback): bool
    {
        // Department heads can delete feedback for bookings in their department
        if ($user->hasRole('department_head') && $feedback->booking && $feedback->booking->department_id === $user->department_id) {
            return !$feedback->is_approved;
        }
        
        // Users can delete their own feedback if it's not approved
        return $user->id === $feedback->user_id && !$feedback->is_approved;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Feedback $feedback): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Feedback $feedback): bool
    {
        return false;
    }
}
