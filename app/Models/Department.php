<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority_level',
        'description',
        'contact_person',
        'contact_email',
        'contact_phone'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Helper method to get department's active bookings
    public function getActiveBookings()
    {
        return $this->bookings()
            ->where('status', 'approved')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->get();
    }

    // Helper method to get department's pending bookings
    public function getPendingBookings()
    {
        return $this->bookings()
            ->where('status', 'pending')
            ->get();
    }

    // Helper method to get available vehicles for the department
    public function getAvailableVehicles()
    {
        return Vehicle::where('status', 'available')
            ->whereDoesntHave('bookings', function($query) {
                $query->where('status', 'approved')
                    ->where(function($q) {
                        $q->where('start_time', '<=', now())
                            ->where('end_time', '>=', now());
                    });
            })
            ->get();
    }

    public function departmentHead()
    {
        return $this->hasOne(User::class)->whereHas('roles', function($query) {
            $query->where('name', 'department_head');
        });
    }

    public function hasDepartmentHead()
    {
        return $this->departmentHead()->exists();
    }

    public function departmentStaff()
    {
        return $this->hasMany(User::class)->whereHas('roles', function($query) {
            $query->where('name', 'department_staff');
        });
    }
}
