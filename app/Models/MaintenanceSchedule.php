<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'description',
        'scheduled_date',
        'mileage_interval',
        'time_interval_days',
        'status',
        'assigned_to',
        'notes',
        'started_at',
        'completed_at',
        'resolution_notes',
        'parts_used',
        'labor_hours',
        'total_cost'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'labor_hours' => 'float',
        'total_cost' => 'decimal:2',
        'maintenance_type' => 'string',
        'status' => 'string'
    ];

    // Define valid status values
    protected static $validStatuses = [
        'pending',
        'in_progress',
        'completed',
        'cancelled',
        'overdue'
    ];

    protected static $validMaintenanceTypes = [
        'routine_service',
        'inspection',
        'oil_change',
        'tire_rotation',
        'brake_inspection',
        'major_service',
        'repair',           // Added for driver-reported issues
        'emergency_repair', // Added for urgent repairs
        'other'
    ];

    // Custom mutator for status
    public function setStatusAttribute($value)
    {
        if (!in_array($value, self::$validStatuses)) {
            throw new \InvalidArgumentException("Invalid status value: {$value}");
        }
        $this->attributes['status'] = $value;
    }

    public function setMaintenanceTypeAttribute($value)
    {
        if (!in_array($value, self::$validMaintenanceTypes)) {
            throw new \InvalidArgumentException("Invalid maintenance type: {$value}");
        }
        $this->attributes['maintenance_type'] = $value;
    }

    public static function getValidStatuses()
    {
        return self::$validStatuses;
    }

    public static function getValidMaintenanceTypes()
    {
        return self::$validMaintenanceTypes;
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helper method to check if maintenance is due
    public function isDue()
    {
        if (!in_array($this->status, ['pending', 'overdue'])) {
            return false;
        }

        $isDueByDate = $this->scheduled_date && $this->scheduled_date->isPast();
        
        if ($this->mileage_interval) {
            $isDueByMileage = $this->vehicle->current_mileage >= 
                ($this->vehicle->last_maintenance_mileage + $this->mileage_interval);
            return $isDueByDate || $isDueByMileage;
        }

        return $isDueByDate;
    }

    // Helper method to get next maintenance date
    public function getNextMaintenanceDate()
    {
        if ($this->status !== 'completed') {
            return $this->scheduled_date;
        }

        return $this->scheduled_date->addDays($this->time_interval_days);
    }

    // Helper method to check if schedule is recurring
    public function isRecurring()
    {
        return $this->time_interval_days > 0 || $this->mileage_interval > 0;
    }

    // Helper methods for status checks
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    // Get status color for badges
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'overdue' => 'danger'
        ][$this->status] ?? 'secondary';
    }
}
