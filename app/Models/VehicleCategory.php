<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'priority_level'
    ];

    public function types()
    {
        return $this->hasMany(VehicleType::class, 'category_id');
    }

    public function vehicles()
    {
        return $this->hasManyThrough(Vehicle::class, VehicleType::class, 'category_id', 'type_id');
    }

    // Helper method to get departments that can access this category
    public function getEligibleDepartments()
    {
        return Department::where('priority_level', '>=', $this->priority_level)->get();
    }
}
