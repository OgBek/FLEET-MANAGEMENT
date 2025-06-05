<?php

namespace App\Http\Controllers\Admin;

use App\Models\VehicleBrand;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleBrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $brands = VehicleBrand::withCount('vehicles')->latest()->paginate(10);
        return view('vehicle-brands.index', compact('brands'));
    }

    public function create()
    {
        return view('vehicle-brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_brands,name',
            'manufacturer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        VehicleBrand::create($validated);

        return redirect()->route('admin.vehicle-brands.index')
            ->with('success', 'Vehicle brand created successfully.');
    }

    public function edit(VehicleBrand $vehicleBrand)
    {
        return view('vehicle-brands.edit', compact('vehicleBrand'));
    }

    public function update(Request $request, VehicleBrand $vehicleBrand)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_brands', 'name')->ignore($vehicleBrand->id)
            ],
            'manufacturer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $vehicleBrand->update($validated);

        return redirect()->route('admin.vehicle-brands.index')
            ->with('success', 'Vehicle brand updated successfully.');
    }

    public function destroy(VehicleBrand $vehicleBrand)
    {
        if ($vehicleBrand->vehicles()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete brand with associated vehicles.');
        }

        $vehicleBrand->delete();

        return redirect()->route('admin.vehicle-brands.index')
            ->with('success', 'Vehicle brand deleted successfully.');
    }
}
