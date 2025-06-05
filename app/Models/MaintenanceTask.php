<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'maintenance_type',
        'priority',
        'status',
        'scheduled_date',
        'due_date',
        'estimated_hours',
        'vehicle_id',
        'vehicle_report_id',
        'assigned_to',
        'created_by',
        'completed_by',
        'started_at',
        'completed_at',
        'resolution_notes',
        'parts_used',
        'labor_hours',
        'total_cost'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'labor_hours' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    /**
     * Get the user who completed the task.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vehicleReport(): BelongsTo
    {
        return $this->belongsTo(VehicleReport::class);
    }

    public function isDue(): bool
    {
        return $this->status !== 'completed' && 
               $this->scheduled_date < now();
    }
    
    /**
     * Get the task's history.
     */
    public function history()
    {
        return $this->hasMany(\App\Models\Activity::class, 'subject_id')
            ->where('subject_type', self::class)
            ->orWhere(function($query) {
                $query->where('subject_type', 'App\\Models\\MaintenanceTask')
                      ->where('subject_id', $this->id);
            })
            ->latest();
    }
} 