<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\ImageHandler;
use App\Models\Activity;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, ImageHandler;

    protected $fillable = [
        'registration_number',
        'model',
        'year',
        'capacity',
        'type_id',
        'brand_id',
        'status',
        'image_data',
        'color',
        'vin_number',
        'engine_number',
        'fuel_type',
        'current_mileage',
        'initial_mileage',
        'maintenance_interval',
        'features',
        'insurance_expiry',
        'last_maintenance_date',
        'notes',
        'assigned_driver_id'
    ];

    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'current_mileage' => 'integer',
        'initial_mileage' => 'integer',
        'maintenance_interval' => 'integer',
        'insurance_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'status' => 'string'
    ];

    protected $appends = ['image_url', 'formatted_brand'];

    // Define valid status values
    public static $validStatuses = [
        'available',    // Vehicle is ready for booking
        'maintenance',  // Vehicle is under maintenance
        'booked',      // Vehicle has future bookings
        'in_use',      // Vehicle is currently being used in a trip
        'out_of_service' // Vehicle is not available for any bookings
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-uppercase VIN and engine numbers
        static::saving(function ($vehicle) {
            if ($vehicle->isDirty('vin_number')) {
                $vehicle->vin_number = strtoupper($vehicle->vin_number);
            }
            if ($vehicle->isDirty('engine_number')) {
                $vehicle->engine_number = strtoupper($vehicle->engine_number);
            }
        });

        // When vehicle status changes, update related bookings and log activity
        static::updating(function ($vehicle) {
            if ($vehicle->isDirty('status')) {
                $oldStatus = $vehicle->getOriginal('status');
                $newStatus = $vehicle->status;
                $user = auth()->user();

                // If vehicle becomes unavailable, cancel pending bookings
                if (in_array($newStatus, ['maintenance', 'out_of_service']) && !in_array($oldStatus, ['maintenance', 'out_of_service'])) {
                    $vehicle->bookings()
                        ->where('status', 'pending')
                        ->update(['status' => 'cancelled']);
                    
                    // Log maintenance activity
                    if ($newStatus === 'maintenance') {
                        Activity::log(
                            $user,
                            $vehicle,
                            'maintenance',
                            "Vehicle {$vehicle->registration_number} has been sent for maintenance"
                        );
                    } else {
                        Activity::log(
                            $user,
                            $vehicle,
                            'maintenance',
                            "Vehicle {$vehicle->registration_number} has been marked as out of service"
                        );
                    }
                }

                // Log when vehicle returns to service
                if ($newStatus === 'available' && in_array($oldStatus, ['maintenance', 'out_of_service'])) {
                    Activity::log(
                        $user,
                        $vehicle,
                        'maintenance',
                        "Vehicle {$vehicle->registration_number} is now available for bookings"
                    );
                }
            }

            // Log maintenance date changes
            if ($vehicle->isDirty('last_maintenance_date')) {
                Activity::log(
                    auth()->user(),
                    $vehicle,
                    'maintenance',
                    "Maintenance completed for vehicle {$vehicle->registration_number}"
                );
            }
        });

        // Log new vehicle creation
        static::created(function ($vehicle) {
            Activity::log(
                auth()->user(),
                $vehicle,
                'vehicle',
                "New vehicle {$vehicle->registration_number} has been added to the fleet"
            );
        });
    }

    // Custom mutator for status
    public function setStatusAttribute($value)
    {
        // Ensure status is one of the valid values
        if (in_array($value, self::$validStatuses)) {
            $this->attributes['status'] = $value;
        } else {
            throw new \InvalidArgumentException("Invalid vehicle status value: {$value}");
        }
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_data) {
            // If it's already a base64 string, return as is
            if (strpos($this->image_data, 'data:image') === 0) {
                return $this->image_data;
            }
            // If it's a URL, return as is
            if (filter_var($this->image_data, FILTER_VALIDATE_URL)) {
                return $this->image_data;
            }
            // If it's a path, convert to URL
            return asset($this->image_data);
        }
        return null;
    }

    public function getFormattedBrandAttribute()
    {
        if (!isset($this->brand)) {
            return 'Unknown Brand';
        }

        if (is_object($this->brand) && isset($this->brand->name)) {
            return $this->brand->name;
        } else if (is_string($this->brand)) {
            $decoded = json_decode($this->brand, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['name'])) {
                return $decoded['name'];
            }
            return $this->brand;
        } else if (is_array($this->brand) && isset($this->brand['name'])) {
            return $this->brand['name'];
        }
        
        return 'Unknown Brand';
    }

    public function isAvailableForBooking($startTime = null, $endTime = null): bool
    {
        // Check if vehicle is in a non-bookable status
        if (in_array($this->status, ['maintenance', 'out_of_service', 'in_use'])) {
            return false;
        }

        // If no dates provided, just check if vehicle is available
        if (!$startTime || !$endTime) {
            return $this->status === 'available';
        }

        // Convert string dates to Carbon instances if needed
        if (is_string($startTime)) {
            $startTime = Carbon::parse($startTime);
        }
        if (is_string($endTime)) {
            $endTime = Carbon::parse($endTime);
        }

        // Check if there are any overlapping approved or in-progress bookings
        return !$this->bookings()
            ->whereIn('status', ['approved', 'in_progress'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();
        }

    public function updateBookingStatus()
    {
        // Get the current active booking if any
        $currentBooking = $this->bookings()
            ->where('status', 'in_progress')
            ->first();

        if ($currentBooking) {
            $this->update(['status' => 'in_use']);
            return;
        }

        // Check for future approved bookings
        $hasApprovedBookings = $this->bookings()
            ->where('status', 'approved')
            ->where('start_time', '>', now())
            ->exists();

        $this->update([
            'status' => $hasApprovedBookings ? 'booked' : 'available'
        ]);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function type()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function maintenanceTasks()
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
    
    /**
     * Get all vehicle issue reports for this vehicle
     */
    public function vehicleReports()
    {
        return $this->hasMany(VehicleReport::class);
    }

    public function getTypeCategory()
    {
        return $this->type->category ?? null;
    }

    public function getUpcomingMaintenance()
    {
        return $this->maintenanceSchedules()
            ->where('status', 'pending')
            ->orderBy('scheduled_date')
            ->first();
    }

    public function isMaintenanceDue()
    {
        $lastMaintenance = $this->last_maintenance_date;
        if (!$lastMaintenance || !$this->maintenance_interval) {
            return false;
        }

        return $lastMaintenance->addDays($this->maintenance_interval)->isPast();
    }

    public function assignedDriver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    /**
     * Prepare the model for array or JSON serialization.
     * This ensures that brand is properly formatted in API responses.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // If brand is a JSON string or an array, replace it with a formatted version
        if (isset($array['brand']) && !is_null($array['brand'])) {
            if (!is_array($array['brand']) && !is_object($array['brand'])) {
                $decoded = json_decode($array['brand'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $array['brand'] = $decoded;
                }
            }
        }
        
        return $array;
    }

    /**
     * Mark the vehicle as available for booking
     */
    public function markAvailable()
    {
        $this->update(['status' => 'available']);
        return $this;
    }

    /**
     * Mark the vehicle as under maintenance
     */
    public function markUnderMaintenance()
    {
        $this->update(['status' => 'maintenance']);
        return $this;
    }
}
