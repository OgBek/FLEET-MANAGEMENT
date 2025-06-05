<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_staff_id',
        'service_type',
        'priority',
        'scheduled_date',
        'estimated_completion_date',
        'description',
        'parts_required',
        'estimated_cost',
        'status',
        'service_date',
        'cost',
        'odometer_reading',
        'parts_replaced',
        'labor_hours',
        'next_service_date',
        'notes'
    ];

    protected $casts = [
        'service_date' => 'date',
        'scheduled_date' => 'date',
        'estimated_completion_date' => 'date',
        'next_service_date' => 'date',
        'cost' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'parts_replaced' => 'array'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceStaff()
    {
        return $this->belongsTo(User::class, 'maintenance_staff_id');
    }

    // Helper method to calculate total maintenance cost
    public function getTotalCost()
    {
        return $this->cost;
    }

    // Helper method to check if next service is due
    public function isNextServiceDue()
    {
        return $this->next_service_date && $this->next_service_date->isPast();
    }

    // Helper method to get maintenance duration in hours
    public function getDurationInHours()
    {
        return $this->labor_hours ?? 0;
    }
}
