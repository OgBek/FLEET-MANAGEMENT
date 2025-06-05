<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\VehicleBrand;
use App\Models\VehicleCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Department;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewVehicleAdded;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::with(['type.category', 'brand'])
            ->latest()
            ->paginate(3);

        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        $types = VehicleType::with('category')->get();
        $brands = VehicleBrand::all();
        
        return view('admin.vehicles.create', compact('types', 'brands'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        // Convert VIN and engine numbers to uppercase before validation
        $data = $request->all();
        if (isset($data['vin_number'])) {
            $data['vin_number'] = strtoupper($data['vin_number']);
        }
        if (isset($data['engine_number'])) {
            $data['engine_number'] = strtoupper($data['engine_number']);
        }
        $request->merge($data);

        $validatedData = $request->validate([
            'registration_number' => 'required|string|max:255|unique:vehicles',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'type_id' => 'required|exists:vehicle_types,id',
            'brand_id' => 'required|exists:vehicle_brands,id',
            'capacity' => 'required|integer|min:1',
            'fuel_type' => 'required|string|in:petrol,diesel,electric,hybrid',
            'initial_mileage' => 'required|numeric|min:0',
            'maintenance_interval' => 'required|integer|min:0',
            'vin_number' => ['required', 'string', 'max:255', 'unique:vehicles', 'regex:/^[A-Z0-9]+$/'],
            'engine_number' => ['required', 'string', 'max:255', 'unique:vehicles', 'regex:/^[A-Z0-9]+$/'],
            'color' => 'required|string|max:50',
            'current_mileage' => 'required|integer|min:0',
            'features' => 'nullable|string',
            'insurance_expiry' => 'required|date|after:today',
            'last_maintenance_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:available,booked,maintenance,out_of_service',
            'image' => ['nullable', 'string']
        ], [
            'vin_number.regex' => 'The VIN number must only contain uppercase letters and numbers (no special characters or spaces).',
            'engine_number.regex' => 'The engine number must only contain uppercase letters and numbers (no special characters or spaces).'
        ]);

        if ($request->filled('image')) {
            if (str_starts_with($validatedData['image'], 'data:image/')) {
                $validatedData['image_data'] = $validatedData['image'];
            }
            unset($validatedData['image']);
        }

        $vehicle = Vehicle::create($validatedData);

        // Notify all clients (non-admin users) about the new vehicle
        try {
            $clients = User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($clients as $client) {
                $client->notify(new NewVehicleAdded($vehicle));
            }

            Log::info('Vehicle notification sent successfully', [
                'vehicle_id' => $vehicle->id,
                'client_count' => $clients->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send vehicle notification', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle added successfully. Notifications have been sent to all clients.');
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->loadMissing([
            'type.category',
            'brand',
            'bookings.requestedBy',
            'maintenanceRecords',
            'maintenanceSchedules'
        ]);

        // Calculate vehicle statistics
        $stats = [
            'total_bookings' => $vehicle->bookings()->count(),
            'total_maintenance' => $vehicle->maintenanceTasks()->whereIn('status', ['in_progress', 'completed'])->count() +
                                  $vehicle->maintenanceSchedules()->whereIn('status', ['in_progress', 'completed'])->count(),
            'total_distance' => $vehicle->bookings()->sum('actual_distance'),
            'maintenance_cost' => $vehicle->maintenanceRecords()->sum('cost') +
                                 $vehicle->maintenanceTasks()->whereIn('status', ['in_progress', 'completed'])->sum('total_cost') +
                                 $vehicle->maintenanceSchedules()->whereIn('status', ['in_progress', 'completed'])->sum('total_cost'),
            'upcoming_maintenance' => $vehicle->maintenanceSchedules()
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->first(),
            'days_until_insurance' => Carbon::now()->diffInDays($vehicle->insurance_expiry, false)
        ];

        return view('admin.vehicles.show', compact('vehicle', 'stats'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        $types = VehicleType::with('category')->get();
        $brands = VehicleBrand::all();
        $departments = Department::all();
        
        return view('admin.vehicles.edit', compact('vehicle', 'types', 'brands', 'departments'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // Convert VIN and engine numbers to uppercase before validation
        $data = $request->all();
        if (isset($data['vin_number'])) {
            $data['vin_number'] = strtoupper($data['vin_number']);
        }
        if (isset($data['engine_number'])) {
            $data['engine_number'] = strtoupper($data['engine_number']);
        }
        $request->merge($data);

        $validated = $request->validate([
            'registration_number' => ['required', 'string', 'max:255', Rule::unique('vehicles')->ignore($vehicle)],
            'vin_number' => ['required', 'string', 'max:255', Rule::unique('vehicles')->ignore($vehicle), 'regex:/^[A-Z0-9]+$/'],
            'engine_number' => ['required', 'string', 'max:255', Rule::unique('vehicles')->ignore($vehicle), 'regex:/^[A-Z0-9]+$/'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'capacity' => ['required', 'integer', 'min:1'],
            'type_id' => ['required', 'exists:vehicle_types,id'],
            'brand_id' => ['required', 'exists:vehicle_brands,id'],
            'color' => ['required', 'string', 'max:50'],
            'fuel_type' => ['required', 'string', 'in:petrol,diesel,electric,hybrid'],
            'current_mileage' => ['required', 'integer', 'min:0'],
            'initial_mileage' => ['required', 'integer', 'min:0'],
            'maintenance_interval' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'string'],
            'insurance_expiry' => ['required', 'date', 'after:today'],
            'last_maintenance_date' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:available,maintenance,out_of_service'],
            'image' => ['nullable', 'string'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'vin_number.regex' => 'The VIN number must only contain uppercase letters and numbers (no special characters or spaces).',
            'engine_number.regex' => 'The engine number must only contain uppercase letters and numbers (no special characters or spaces).'
        ]);

        // Handle image update
        if ($request->filled('image')) {
            if (str_starts_with($validated['image'], 'data:image/')) {
                $validated['image_data'] = $validated['image'];
            }
        }
        unset($validated['image']);

        // Handle image removal
        if ($request->boolean('remove_image')) {
            $validated['image_data'] = null;
        }
        unset($validated['remove_image']);

        $vehicle->update($validated);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }
}
