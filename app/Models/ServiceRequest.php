<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ServiceRequestStatusUpdated;
use App\Notifications\NewServiceRequest;
use App\Notifications\ServiceRequestCompleted;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_title',
        'issue_description',
        'priority',
        'status',
        'scheduled_date',
        'vehicle_id',
        'requested_by',
        'assigned_to',
        'resolution_notes',
        'parts_used',
        'labor_hours',
        'total_cost',
        'completed_at',
        'additional_notes',
        'admin_notes',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'started_at'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'started_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Helper method to check if request is overdue
    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_COMPLETED || $this->status === self::STATUS_REJECTED) {
            return false;
        }

        $dueDate = $this->getDueDate();
        return $dueDate && now()->gt($dueDate);
    }

    // Helper method to get due date based on priority
    public function getDueDate()
    {
        if (!$this->scheduled_date) {
            return null;
        }

        $priorityDays = [
            'low' => 7,
            'medium' => 3,
            'high' => 1,
            'urgent' => 0
        ];

        return $this->scheduled_date->addDays($priorityDays[$this->priority] ?? 7);
    }

    // Helper method to get time to resolution
    public function getResolutionTime()
    {
        if (!$this->completed_at || !$this->scheduled_date) {
            return null;
        }

        return $this->scheduled_date->diffInHours($this->completed_at);
    }

    // Approve the service request
    public function approve(User $admin, User $maintenanceStaff, ?string $notes = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'assigned_to' => $maintenanceStaff->id,
            'admin_notes' => $notes
        ]);

        // Notify the maintenance staff
        $this->notifyMaintenanceStaff($oldStatus, $this->status);

        // Notify the requester
        $this->notifyRequester($oldStatus, $this->status);
        
        return $this;
    }

    // Reject the service request
    public function reject(User $admin, string $reason)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_by' => $admin->id,
            'rejection_reason' => $reason
        ]);

        // Notify the requester
        $this->notifyRequester($oldStatus, $this->status, $reason);
        
        return $this;
    }

    // Start working on the service request
    public function startWork()
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        // Mark the vehicle as under maintenance
        $this->vehicle->markUnderMaintenance();

        // Notify relevant users
        $this->notifyRequester($oldStatus, 'in_progress');
        
        return $this;
    }

    // Complete the service request
    public function complete(array $data)
    {
        $oldStatus = $this->status;
        
        // Start a database transaction
        \DB::beginTransaction();
        
        try {
            // Update the service request
            $this->update([
                'status' => 'completed',
                'completed_at' => now(),
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'parts_used' => $data['parts_used'] ?? null,
                'labor_hours' => $data['labor_hours'] ?? null,
                'total_cost' => $data['total_cost'] ?? 0
            ]);

            // Mark related maintenance tasks as completed
            $this->maintenanceTasks()->update([
                'status' => 'completed',
                'completed_at' => now(),
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'parts_used' => $data['parts_used'] ?? null,
                'labor_hours' => $data['labor_hours'] ?? null,
                'total_cost' => $data['total_cost'] ?? 0
            ]);

            // Mark the vehicle as available immediately after completion
            $this->vehicle->markAvailable();

            // Commit the transaction
            \DB::commit();

            // Notify relevant users about completion
            $this->notifyCompletion($oldStatus);
            
            return $this;
            
        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Notify relevant users about service request completion
     */
    protected function notifyCompletion($oldStatus)
    {
        // Notify the requester
        $this->notifyRequester($oldStatus, 'completed');
        
        // Notify admins
        $this->notifyAdmins();
        
        // Notify the driver if assigned to the vehicle
        if ($this->vehicle->driver) {
            $this->notifyDriver();
        }
    }

    /**
     * Notify the vehicle's driver about service request completion
     */
    protected function notifyDriver()
    {
        $driver = $this->vehicle->driver;
        if ($driver) {
            $driver->notify(new ServiceRequestCompleted($this));
        }
    }

    /**
     * Notify admins about service request updates
     */
    public function notifyAdmins()
    {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ServiceRequestCompleted($this, null, $this->status));
        }
    }

    /**
     * Notify the requester about service request updates
     */
    protected function notifyRequester($oldStatus, $newStatus)
    {
        if ($this->requestedBy) {
            $this->requestedBy->notify(new \App\Notifications\ServiceRequestCompleted($this, $oldStatus, $newStatus));
        }
    }

    // Notify maintenance staff about new or updated requests
    protected function notifyMaintenanceStaff($oldStatus = null, $newStatus = null, $reason = null)
    {
        $maintenanceStaff = User::role('maintenance_staff')->get();
        Notification::send($maintenanceStaff, new ServiceRequestStatusUpdated($this, $oldStatus ?? 'new', $newStatus ?? $this->status, $reason));
    }

    // Check if the request is pending admin approval
    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Check if the request is approved
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // Check if the request is rejected
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // Check if the request is in progress
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    // Check if the request is completed
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get the maintenance tasks for the service request.
     */
    public function maintenanceTasks()
    {
        $query = $this->hasMany(\App\Models\MaintenanceTask::class, 'vehicle_id', 'vehicle_id');
        
        if ($this->scheduled_date) {
            $query->whereDate('scheduled_date', $this->scheduled_date);
        }
        
        return $query->orderBy('scheduled_date');
    }

    /**
     * Get the maintenance records visible to admin and driver
     */
    public function scopeMaintenanceRecords($query)
    {
        return $query->whereIn('status', ['pending', 'approved', 'in_progress', 'completed'])
                    ->with(['vehicle', 'requestedBy', 'assignedTo']);
    }
}
