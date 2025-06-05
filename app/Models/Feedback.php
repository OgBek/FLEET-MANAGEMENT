<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Booking;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'content',
        'rating',
        'status',
        'is_public',
        'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_public' => 'boolean',
        'is_approved' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
