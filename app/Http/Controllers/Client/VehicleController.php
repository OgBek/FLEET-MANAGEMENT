<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:department_head|department_staff');
    }

    public function index()
    {
        // Show only available vehicles
        $vehicles = Vehicle::with(['type.category', 'brand'])
            ->where('status', 'available')
            ->latest()
            ->paginate(3);

        return view('client.vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        // Load the necessary relationships
        $vehicle->load(['type.category', 'brand']);

        // Check if the vehicle exists and is accessible
        if (!$vehicle) {
            return redirect()->route('client.vehicles.index')
                ->with('error', 'Vehicle not found.');
        }

        // Show the vehicle details
        return view('client.vehicles.show', compact('vehicle'));
    }
}