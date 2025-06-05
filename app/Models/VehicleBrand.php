<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'manufacturer'
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'brand_id');
    }

    // Helper method to get count of vehicles by status
    public function getVehicleStatusCount()
    {
        return $this->vehicles()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
}
