<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleReport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:driver');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = VehicleReport::where('user_id', auth()->id())
            ->with(['vehicle.brand', 'vehicle.type.category'])
            ->latest()
            ->paginate(5);

        return view('driver.vehicle-reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all vehicles regardless of status
        $vehicles = Vehicle::all();
        
        if ($vehicles->isEmpty()) {
            return redirect()
                ->route('driver.dashboard')
                ->with('error', 'No vehicles found in the system.');
        }

        return view('driver.vehicle-reports.create', compact('vehicles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:mechanical,electrical,body_damage,tire,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high',
            'location' => 'required|string|max:255',
        ]);

        // Merge the validated data with user_id and status
        $reportData = array_merge($validated, [
            'user_id' => auth()->id(),
            'status' => 'pending' // This will be properly quoted in the query
        ]);
        
        $report = VehicleReport::create($reportData);

        // Update vehicle status if severity is high
        if ($validated['severity'] === 'high') {
            $vehicle = Vehicle::find($validated['vehicle_id']);
            $vehicle->update(['status' => 'maintenance']);
        }

        return redirect()
            ->route('driver.vehicle-reports.index')
            ->with('success', 'Vehicle report submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if the user is authorized to view this report
            if (auth()->id() !== $vehicleReport->user_id) {
                return redirect()
                    ->route('driver.dashboard')
                    ->with('error', 'You are not authorized to view this report.');
            }
            
            $vehicleReport->load(['vehicle.brand', 'vehicle.type.category']);
            return view('driver.vehicle-reports.show', compact('vehicleReport'));
            
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('driver.dashboard')
                ->with('error', 'The vehicle report you are looking for has been deleted or does not exist.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if the user is authorized to edit this report
            if (auth()->id() !== $vehicleReport->user_id) {
                return redirect()
                    ->route('driver.dashboard')
                    ->with('error', 'You are not authorized to edit this report.');
            }
            
            if ($vehicleReport->status !== 'pending') {
                return redirect()
                    ->route('driver.vehicle-reports.index')
                    ->with('error', 'You can only edit pending reports.');
            }
            
            $vehicles = Vehicle::all();
            return view('driver.vehicle-reports.edit', compact('vehicleReport', 'vehicles'));
            
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('driver.dashboard')
                ->with('error', 'The vehicle report you are trying to edit has been deleted or does not exist.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if the user is authorized to update this report
            if (auth()->id() !== $vehicleReport->user_id) {
                return redirect()
                    ->route('driver.dashboard')
                    ->with('error', 'You are not authorized to update this report.');
            }
            
            if ($vehicleReport->status !== 'pending') {
                return redirect()
                    ->route('driver.vehicle-reports.index')
                    ->with('error', 'You can only edit pending reports.');
            }

            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'type' => 'required|in:mechanical,electrical,body_damage,tire,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'required|in:low,medium,high',
                'location' => 'required|string|max:255',
            ]);

            $vehicleReport->update($validated);

            return redirect()
                ->route('driver.vehicle-reports.show', $vehicleReport)
                ->with('success', 'Vehicle report updated successfully.');
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('driver.dashboard')
                ->with('error', 'The vehicle report you are trying to update has been deleted or does not exist.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if the user is authorized to delete this report
            if (auth()->id() !== $vehicleReport->user_id) {
                return redirect()
                    ->route('driver.dashboard')
                    ->with('error', 'You are not authorized to delete this report.');
            }
            
            if ($vehicleReport->status !== 'pending') {
                return redirect()
                    ->route('driver.vehicle-reports.index')
                    ->with('error', 'You can only delete pending reports.');
            }

            $vehicleReport->delete();

            return redirect()
                ->route('driver.vehicle-reports.index')
                ->with('success', 'Vehicle report deleted successfully.');
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('driver.vehicle-reports.index')
                ->with('info', 'The vehicle report was already deleted.');
        }
    }
}
