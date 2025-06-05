<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'maintenance_staff', 'driver']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VehicleReport $vehicleReport): bool
    {
        // Admin can view all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Maintenance staff can view reports assigned to them
        if ($user->hasRole('maintenance_staff')) {
            return $vehicleReport->maintenanceSchedule && 
                   $vehicleReport->maintenanceSchedule->assigned_to === $user->id;
        }

        // Drivers can view their own reports
        if ($user->hasRole('driver')) {
            return $user->id === $vehicleReport->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'driver']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VehicleReport $vehicleReport): bool
    {
        // Admin can update all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Maintenance staff can update reports assigned to them
        if ($user->hasRole('maintenance_staff')) {
            return $vehicleReport->maintenanceSchedule && 
                   $vehicleReport->maintenanceSchedule->assigned_to === $user->id;
        }

        // Drivers can update their own reports only if they're still pending
        if ($user->hasRole('driver')) {
            return $user->id === $vehicleReport->user_id && 
                   $vehicleReport->status === 'pending';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VehicleReport $vehicleReport): bool
    {
        // Only admins can delete reports
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the status of the model.
     */
    public function updateStatus(User $user, VehicleReport $vehicleReport): bool
    {
        // Admin can update status of all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Maintenance staff can update status of reports assigned to them
        if ($user->hasRole('maintenance_staff')) {
            return $vehicleReport->maintenanceSchedule && 
                   $vehicleReport->maintenanceSchedule->assigned_to === $user->id;
        }

        return false;
    }
}
