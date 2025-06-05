<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\VehicleReport;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Notifications\VehicleReportStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleReportController extends Controller
{
    /**
     * Display a listing of vehicle reports assigned to the maintenance staff.
     */
    public function index()
    {
        $user = Auth::user();
        $vehicleReports = VehicleReport::whereHas('maintenanceSchedule', function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->with(['vehicle', 'driver', 'maintenanceSchedule'])->latest()->paginate(5);

        return view('maintenance.vehicle-reports.index', compact('vehicleReports'));
    }

    /**
     * Display the specified vehicle report.
     */
    public function show($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if maintenance staff is authorized to access this report
            $user = Auth::user();
            $isAssigned = $vehicleReport->maintenanceSchedule && 
                          $vehicleReport->maintenanceSchedule->assigned_to === $user->id;
                          
            if (!$isAssigned) {
                return redirect()
                    ->route('maintenance.dashboard')
                    ->with('error', 'You are not authorized to view this report.');
            }
            
            $vehicleReport->load(['vehicle', 'driver', 'maintenanceSchedule']);
            
            return view('maintenance.vehicle-reports.show', compact('vehicleReport'));
            
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('maintenance.dashboard')
                ->with('error', 'The vehicle report you are looking for has been deleted or does not exist.');
        }
    }

    /**
     * Update the status of a vehicle report.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // Check if maintenance staff is authorized to update this report
            $user = Auth::user();
            $isAssigned = $vehicleReport->maintenanceSchedule && 
                          $vehicleReport->maintenanceSchedule->assigned_to === $user->id;
                          
            if (!$isAssigned) {
                return redirect()
                    ->route('maintenance.dashboard')
                    ->with('error', 'You are not authorized to update this report.');
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,resolved,cancelled',
                'resolution_notes' => 'required_if:status,resolved|nullable|string'
            ]);

            $oldStatus = $vehicleReport->status;
            $vehicleReport->status = $validated['status'];
            
            if ($validated['status'] === 'resolved') {
                $vehicleReport->resolution_notes = $validated['resolution_notes'];
                $vehicleReport->resolved_at = now();
                $vehicleReport->resolved_by = Auth::id();

                // Update maintenance schedule status if it exists
                if ($vehicleReport->maintenanceSchedule) {
                    $vehicleReport->maintenanceSchedule->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'completion_notes' => $validated['resolution_notes']
                    ]);
                }
            }

            $vehicleReport->save();

            // Notify relevant parties about the status update
            $this->notifyStatusUpdate($vehicleReport, $oldStatus);

            return redirect()->route('maintenance.vehicle-reports.show', $vehicleReport)
                ->with('success', 'Vehicle report status updated successfully.');
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('maintenance.dashboard')
                ->with('error', 'The vehicle report you are trying to update has been deleted or does not exist.');
        }
    }

    /**
     * Send notifications about status updates.
     */
    private function notifyStatusUpdate(VehicleReport $vehicleReport, string $oldStatus)
    {
        // Always notify the driver who reported the issue
        if ($vehicleReport->driver) {
            $vehicleReport->driver->notify(new VehicleReportStatusUpdated($vehicleReport, $oldStatus));
        }

        // Notify admins when the report is resolved
        if ($vehicleReport->status === 'resolved') {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new VehicleReportStatusUpdated($vehicleReport, $oldStatus));
            }
        }
    }
}
