<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    /**
     * Display a listing of available vehicles for drivers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $vehicles = Vehicle::with(['type', 'brand'])
            ->where('status', 'available')
            ->latest()
            ->paginate(10);

        return view('driver.vehicles.index', [
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Display the specified vehicle.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\View\View
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['type', 'brand']);
        
        return view('driver.vehicles.show', [
            'vehicle' => $vehicle
        ]);
    }
}
