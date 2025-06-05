<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;
use App\Notifications\VehicleIssueReported;
use Illuminate\Support\Facades\Notification;

class VehicleReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'assigned_to',
        'type',
        'title',
        'description',
        'severity',
        'status',
        'resolution_notes',
        'resolved_at',
        'due_date',
        'completion_date',
        'parts_used',
        'labor_hours',
        'total_cost',
    ];
    
    protected $dates = [
        'resolved_at',
        'due_date',
        'completion_date',
    ];
    
    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'datetime',
        'completion_date' => 'datetime',
        'labor_hours' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($vehicleReport) {
            // Notify all admins when a new vehicle issue is reported
            $admins = User::role('admin')->get();
            Notification::send($admins, new VehicleIssueReported($vehicleReport));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the maintenance task associated with this report.
     */
    public function maintenanceTask()
    {
        return $this->hasOne(\App\Models\MaintenanceTask::class, 'vehicle_report_id');
    }
}
