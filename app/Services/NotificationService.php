<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyClients(Notification $notification)
    {
        $users = User::role(['department_head', 'department_staff'])->get();
        
        Log::info('Sending notifications to clients', [
            'total_users' => $users->count()
        ]);

        foreach ($users as $user) {
            try {
                $user->notify($notification);
                Log::info('Notification sent successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'notification_type' => get_class($notification)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function notifyAdmins(Notification $notification)
    {
        $users = User::role('admin')->get();
        
        Log::info('Sending notifications to admins', [
            'total_users' => $users->count()
        ]);

        foreach ($users as $user) {
            try {
                $user->notify($notification);
                Log::info('Notification sent successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'notification_type' => get_class($notification)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function notifyMaintenanceStaff(Notification $notification)
    {
        $users = User::role('maintenance_staff')->get();
        
        Log::info('Sending notifications to maintenance staff', [
            'total_users' => $users->count()
        ]);

        foreach ($users as $user) {
            try {
                $user->notify($notification);
                Log::info('Notification sent successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'notification_type' => get_class($notification)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 