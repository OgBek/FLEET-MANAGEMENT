<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Activity;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'vehicle_id',
        'driver_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'start_time',
        'end_time',
        'purpose',
        'pickup_location',
        'destination',
        'number_of_passengers',
        'status',
        'rejection_reason',
        'notes',
        'actual_start_time',
        'actual_end_time',
        'completed_at'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($booking) {
            // Handle status transitions
            if ($booking->isDirty('status')) {
                $newStatus = $booking->status;
                $user = auth()->user();
                
                switch ($newStatus) {
                    case self::STATUS_APPROVED:
                        $booking->approved_at = now();
                        $booking->approved_by = auth()->id();
                        
                        // Log approval activity
                        Activity::log(
                            $user,
                            $booking,
                            'booking',
                            "Booking #{$booking->id} for {$booking->vehicle->registration_number} was approved"
                        );
                        break;
                    
                    case self::STATUS_REJECTED:
                        // Log rejection activity
                        Activity::log(
                            $user,
                            $booking,
                            'booking',
                            "Booking #{$booking->id} for {$booking->vehicle->registration_number} was rejected"
                        );
                        break;

                    case self::STATUS_CANCELLED:
                        // Log cancellation activity
                        Activity::log(
                            $user,
                            $booking,
                            'booking',
                            "Booking #{$booking->id} for {$booking->vehicle->registration_number} was cancelled"
                        );
                        break;
                    
                    case self::STATUS_IN_PROGRESS:
                        if (!$booking->actual_start_time) {
                            $booking->actual_start_time = now();
                        }
                        
                        // Log trip start activity
                        Activity::log(
                            $user,
                            $booking,
                            'booking',
                            "Trip started for booking #{$booking->id} with {$booking->vehicle->registration_number}"
                        );
                        break;
                    
                    case self::STATUS_COMPLETED:
                        if (!$booking->actual_end_time) {
                            $booking->actual_end_time = now();
                        }
                        if (!$booking->completed_at) {
                            $booking->completed_at = now();
                        }
                        
                        // Log trip completion activity
                        Activity::log(
                            $user,
                            $booking,
                            'booking',
                            "Trip completed for booking #{$booking->id} with {$booking->vehicle->registration_number}"
                        );
                        break;
                }
            }
        });
        
        static::created(function ($booking) {
            // Log new booking creation
            Activity::log(
                auth()->user(),
                $booking,
                'booking',
                "New booking #{$booking->id} created for {$booking->vehicle->registration_number}"
            );
        });

        static::updated(function ($booking) {
            // If status changed to completed, rejected, or cancelled, make vehicle and driver available
            if ($booking->wasChanged('status')) {
                $newStatus = $booking->status;
                
                // For completed, rejected, or cancelled statuses, make vehicle and driver available
                if (in_array($newStatus, [self::STATUS_COMPLETED, self::STATUS_REJECTED, self::STATUS_CANCELLED])) {
                    // Update vehicle status to available
                    if ($booking->vehicle_id) {
                        $booking->vehicle()->update(['status' => 'available']);
                    }
                    
                    // Update driver status to available
                    if ($booking->driver_id) {
                        $booking->driver()->update(['is_available' => true]);
                    }
                }
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function startTrip()
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \Exception('Only approved bookings can be started.');
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->actual_start_time = now();
        $this->save();

        // Update vehicle and driver status
        if ($this->vehicle) {
            $this->vehicle->update(['status' => 'in_use']);
        }
        if ($this->driver) {
            $this->driver->update(['is_available' => false]);
        }
    }

    public function completeTrip()
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            throw new \Exception('Only in-progress bookings can be completed.');
        }

        $this->status = self::STATUS_COMPLETED;
        $this->actual_end_time = now();
        $this->completed_at = now();
        $this->save();

        // Make vehicle and driver available
        if ($this->vehicle) {
            $this->vehicle->update(['status' => 'available']);
        }
        if ($this->driver) {
            $this->driver->update(['is_available' => true]);
        }
    }

    public function canBeStarted(): array
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return [
                'can_start' => false,
                'message' => 'This trip cannot be started because it is not approved.'
            ];
        }

        if ($this->end_time->isPast()) {
            return [
                'can_start' => false,
                'message' => 'This trip cannot be started because it has already ended.'
            ];
        }

        return [
            'can_start' => true,
            'message' => 'Trip can be started.'
        ];
    }

    public function getStartTripError(): ?string
    {
        $status = $this->canBeStarted();
        return $status['can_start'] ? null : $status['message'];
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]) && 
               $this->start_time->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS || 
               ($this->status === self::STATUS_APPROVED && 
                now()->between($this->start_time, $this->end_time));
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED || $this->end_time <= now();
    }

    public function markAsCompleted(): void
    {
        if (!$this->isCompleted()) {
            return;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'actual_end_time' => $this->actual_end_time ?? now(),
            'completed_at' => now()
        ]);

        // Make vehicle available
        if ($this->vehicle) {
            $this->vehicle->update(['status' => 'available']);
        }

        // Make driver available
        if ($this->driver) {
            $this->driver->update(['is_available' => true]);
        }
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
