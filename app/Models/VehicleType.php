<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'seating_capacity'
    ];

    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'type_id');
    }

    // Helper method to get available vehicles of this type
    public function getAvailableVehicles()
    {
        return $this->vehicles()->where('status', 'available')->get();
    }
}
