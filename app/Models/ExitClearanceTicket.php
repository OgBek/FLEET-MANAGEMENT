<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExitClearanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'ticket_number',
        'driver_id',
        'vehicle_id',
        'issued_at',
        'valid_until',
        'gate_number',
        'issued_by',
        'remarks',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Get the booking associated with the ticket.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the driver associated with the ticket.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the vehicle associated with the ticket.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the admin who issued the ticket.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
