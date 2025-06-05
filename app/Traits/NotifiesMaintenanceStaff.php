<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Notifications\Notification;

trait NotifiesMaintenanceStaff
{
    protected function notifyMaintenanceStaff(Notification $notification, $assignedTo = null)
    {
        if ($assignedTo) {
            // If a specific staff member is assigned, only notify them
            $assignedTo->notify($notification);
        } else {
            // If no one is assigned, notify all maintenance staff
            $maintenanceStaff = User::role('maintenance_staff')->get();
            
            foreach ($maintenanceStaff as $staff) {
                $staff->notify($notification);
            }
        }
    }
} 