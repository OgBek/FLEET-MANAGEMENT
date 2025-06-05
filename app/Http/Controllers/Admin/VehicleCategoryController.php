<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use App\Models\Department;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of vehicle categories.
     */
    public function index()
    {
        $categories = VehicleCategory::with('types')
            ->latest()
            ->paginate(5);

        return view('admin.vehicle-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new vehicle category.
     */
    public function create()
    {
        $vehicleTypes = VehicleType::all();
        return view('admin.vehicle-categories.create', compact('vehicleTypes'));
    }

    /**
     * Store a newly created vehicle category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_categories'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority_level' => ['required', 'integer', 'min:1', 'max:3'],
            'vehicle_types' => ['nullable', 'array'],
            'vehicle_types.*' => ['exists:vehicle_types,id'],
        ]);

        $category = VehicleCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'priority_level' => $validated['priority_level'],
        ]);

        if (!empty($validated['vehicle_types'])) {
            foreach ($validated['vehicle_types'] as $typeId) {
                VehicleType::find($typeId)->update(['category_id' => $category->id]);
            }
        }

        return redirect()->route('admin.vehicle-categories.index')
            ->with('status', 'Vehicle category created successfully.');
    }

    /**
     * Display the specified vehicle category.
     */
    public function show(VehicleCategory $vehicleCategory)
    {
        $vehicleCategory->load([
            'types' => function ($query) {
                $query->withCount('vehicles');
            },
            'vehicles'
        ]);

        // Get eligible departments based on priority level
        $eligibleDepartments = Department::where('priority_level', '>=', $vehicleCategory->priority_level)
            ->orderBy('priority_level')
            ->get();

        // Get vehicle statistics
        $stats = [
            'total_vehicles' => $vehicleCategory->vehicles->count(),
            'available_vehicles' => $vehicleCategory->vehicles->where('status', 'available')->count(),
            'in_maintenance' => $vehicleCategory->vehicles->where('status', 'maintenance')->count(),
            'total_types' => $vehicleCategory->types->count(),
            'department_access' => $eligibleDepartments->count()
        ];

        return view('admin.vehicle-categories.show', compact(
            'vehicleCategory',
            'eligibleDepartments',
            'stats'
        ));
    }

    /**
     * Show the form for editing the specified vehicle category.
     */
    public function edit(VehicleCategory $vehicleCategory)
    {
        $vehicleTypes = VehicleType::all();
        $vehicleCategory->load('types');
        return view('admin.vehicle-categories.edit', compact('vehicleCategory', 'vehicleTypes'));
    }

    /**
     * Update the specified vehicle category.
     */
    public function update(Request $request, VehicleCategory $vehicleCategory)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_categories')->ignore($vehicleCategory->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority_level' => ['required', 'integer', 'min:1', 'max:3'],
            'vehicle_types' => ['nullable', 'array'],
            'vehicle_types.*' => ['exists:vehicle_types,id'],
        ]);

        $vehicleCategory->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'priority_level' => $validated['priority_level'],
        ]);

        // Get the current vehicle types associated with this category
        $currentTypeIds = $vehicleCategory->types->pluck('id')->toArray();
        
        // Get the new vehicle types from the request
        $newTypeIds = $validated['vehicle_types'] ?? [];
        
        // Find types to remove (types in current but not in new)
        $typesToRemove = array_diff($currentTypeIds, $newTypeIds);
        
        // Find types to add (types in new but not in current)
        $typesToAdd = array_diff($newTypeIds, $currentTypeIds);
        
        // Remove types that are no longer associated
        if (!empty($typesToRemove)) {
            // Find a different category to assign these types to
            $alternativeCategory = VehicleCategory::where('id', '!=', $vehicleCategory->id)
                ->first();
                
            if ($alternativeCategory) {
                VehicleType::whereIn('id', $typesToRemove)
                    ->update(['category_id' => $alternativeCategory->id]);
            }
        }
        
        // Add new types
        if (!empty($typesToAdd)) {
            VehicleType::whereIn('id', $typesToAdd)
                ->update(['category_id' => $vehicleCategory->id]);
        }

        return redirect()->route('admin.vehicle-categories.index')
            ->with('status', 'Vehicle category updated successfully.');
    }

    /**
     * Remove the specified vehicle category.
     */
    public function destroy(VehicleCategory $vehicleCategory)
    {
        // Check if category has any types
        if ($vehicleCategory->types()->exists()) {
            return back()->with('error', 'Cannot delete category with associated vehicle types.');
        }

        $vehicleCategory->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('status', 'Vehicle category deleted successfully.');
    }
} 