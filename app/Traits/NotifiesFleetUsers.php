<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait NotifiesFleetUsers
{
    protected function notifyFleetUsers(Notification $notification)
    {
        Log::info('Starting to notify fleet users');
        
        $users = User::role(['department_head', 'department_staff'])->get();
        
        Log::info('Found users to notify:', [
            'total_users' => $users->count(),
            'user_details' => $users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()
            ])
        ]);
        
        foreach ($users as $user) {
            try {
                if ($notification->shouldNotify($user)) {
                    $user->notify($notification);
                    Log::info('Notification sent successfully', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'notification_type' => get_class($notification)
                    ]);
                } else {
                    Log::info('User should not be notified', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        Log::info('Finished notifying fleet users');
    }
} 