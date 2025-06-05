<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Notifications\Notification;

trait NotifiesAdmins
{
    protected function notifyAdmins(Notification $notification, $booking = null)
    {
        // Notify all admins
        $admins = User::role('admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify($notification);
        }

        // If this is a booking notification and the booking has a department
        if ($booking && $booking->department_id) {
            // Check if the booking was created by a department head
            $requester = $booking->requestedBy;
            $isDepartmentHeadBooking = $requester && $requester->hasRole('department_head');
            
            // Only notify department head if the booking wasn't created by a department head
            if (!$isDepartmentHeadBooking) {
                // Notify the department head
                $departmentHead = User::role('department_head')
                    ->where('department_id', $booking->department_id)
                    ->first();
                    
                if ($departmentHead) {
                    $departmentHead->notify($notification);
                }
            }
        }
    }
}