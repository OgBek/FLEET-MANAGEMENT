<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Support\Facades\Storage;
use App\Models\Booking; // Add this line

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasProfilePhoto;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'department_id',
        'license_number',
        'specialization',
        'status',
        'approval_status',
        'last_active_at',
        'approved_at',
        'status_changed_at',
        'is_available',
        'image_data'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_active_at' => 'datetime',
        'approved_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'is_available' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->image_data ? $this->image_data : $this->defaultProfilePhotoUrl();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function bookingsRequested()
    {
        return $this->hasMany(Booking::class, 'requested_by');
    }

    public function bookingsAssigned()
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }

    public function bookingsApproved()
    {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'maintenance_staff_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'requested_by');
    }

    public function assignedServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_to');
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class, 'assigned_to');
    }

    public function assignedVehicle()
    {
        return $this->hasOne(Vehicle::class, 'assigned_driver_id');
    }

    /**
     * Get all bookings associated with the user in any capacity.
     * This combines requested, assigned, and approved bookings.
     */
    public function bookings()
    {
        $userId = $this->id;
        
        return Booking::where(function($query) use ($userId) {
            $query->where('requested_by', $userId)
                  ->orWhere('driver_id', $userId)
                  ->orWhere('approved_by', $userId);
        });
    }

    // Approval Methods
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function approve()
    {
        $this->update([
            'approval_status' => 'approved',
            'status' => 'active',
            'approved_at' => now()
        ]);
    }

    public function reject()
    {
        $this->update([
            'approval_status' => 'rejected',
            'status' => 'inactive',
            'approved_at' => null
        ]);
    }

    // Helper method to get active bookings
    public function getActiveBookings()
    {
        if ($this->hasRole('driver')) {
            return $this->bookingsAssigned()->whereIn('status', ['approved'])->get();
        }
        return $this->bookingsRequested()->whereIn('status', ['approved'])->get();
    }

    /**
     * Get the security questions answered by the user.
     */
    public function securityQuestions()
    {
        return $this->belongsToMany(SecurityQuestion::class, 'user_security_answers')
            ->withPivot('answer', 'hashed_answer')
            ->withTimestamps();
    }

    /**
     * Get the security answers provided by the user.
     */
    public function securityAnswers()
    {
        return $this->hasMany(UserSecurityAnswer::class);
    }

    // Helper method to get pending approvals (for admin and department heads)
    public function getPendingApprovals()
    {
        if ($this->hasRole('admin')) {
            // Admins see all pending bookings
            return Booking::where('status', 'pending')->get();
        } elseif ($this->hasRole('department_head')) {
            // Department heads should only see bookings that require their approval
            // This means:
            // 1. Bookings from their department
            // 2. NOT created by themselves
            // 3. NOT created by other department heads (as those go to admin)
            return Booking::where('status', 'pending')
                ->where('department_id', $this->department_id)
                ->where('requested_by', '!=', $this->id) // Exclude own bookings
                ->whereDoesntHave('requestedBy', function($query) {
                    $query->role('department_head'); // Exclude other department head bookings
                })
                ->get();
        }
        return collect();
    }

    // Helper method to get assigned maintenance tasks
    public function getAssignedMaintenanceTasks()
    {
        if (!$this->hasRole('maintenance_staff')) {
            return collect();
        }

        return $this->maintenanceSchedules()
            ->where('status', 'pending')
            ->orderBy('scheduled_date')
            ->get();
    }

    // Helper method to check if user can book vehicles
    public function canBookVehicles()
    {
        return $this->hasAnyRole(['department_head', 'department_staff']);
    }

    // Helper method to check if user can approve bookings
    public function canApproveBookings()
    {
        return $this->hasAnyRole(['admin', 'department_head']);
    }

    public function isAvailableForBooking(): bool
    {
        return $this->hasRole('driver') && 
               $this->is_available && 
               $this->status === 'active' &&
               !$this->hasActiveBooking();
    }

    public function hasActiveBooking(): bool
    {
        return $this->driverBookings()
            ->where('status', 'approved')
            ->where('end_time', '>', now())
            ->exists();
    }

    public function driverBookings()
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }

    public function toggleAvailability()
    {
        $this->update(['is_available' => !$this->is_available]);
    }

    /**
     * Check if the driver has an active trip
     */
    public function hasActiveTrip(): bool
    {
        if (!$this->hasRole('driver')) {
            return false;
        }

        return $this->driverBookings()
            ->where('status', 'in_progress')
            ->exists();
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function hasUnreadNotifications(): bool
    {
        return $this->unreadNotifications()->exists();
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function sendNotification($type, $message, $link = null, $data = null)
    {
        $notificationData = [
            'type' => $type,
            'message' => $message,
            'link' => $link,
            'data' => $data
        ];

        $this->notify(new \Illuminate\Notifications\DatabaseNotification($notificationData));
    }

    public function sendNotificationToRole($role, $type, $message, $link = null, $data = null)
    {
        $users = User::role($role)->get();
        foreach ($users as $user) {
            $user->sendNotification($type, $message, $link, $data);
        }
    }

    public function updateProfilePhoto($photo)
    {
        if ($photo instanceof \Illuminate\Http\UploadedFile) {
            // Read file as binary data and convert to base64 encoded data URL
            $fileContents = file_get_contents($photo->getRealPath());
            $base64 = base64_encode($fileContents);
            $mime = $photo->getMimeType();
            $this->image_data = "data:{$mime};base64,{$base64}";
            $this->save();
        } elseif (is_string($photo) && str_starts_with($photo, 'data:image')) {
            $this->image_data = $photo;
            $this->save();
        }
    }

    public function deleteProfilePhoto()
    {
        $this->forceFill([
            'image_data' => null,
        ])->save();
    }
}
