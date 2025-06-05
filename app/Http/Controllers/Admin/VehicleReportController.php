<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleReport;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\MaintenanceSchedule;
use App\Models\Activity;

use App\Notifications\VehicleIssueApproved;
use App\Notifications\VehicleReportStatusUpdated;
use App\Notifications\NewMaintenanceTaskAssigned;
use Illuminate\Support\Facades\DB;
use App\Traits\NotifiesMaintenanceStaff;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleReportController extends Controller
{
    use NotifiesMaintenanceStaff;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of vehicle reports.
     */
    public function index()
    {
        $reports = VehicleReport::with(['vehicle.brand', 'vehicle.type.category', 'driver'])
            ->latest()
            ->paginate(7);

        return view('admin.vehicle-reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new vehicle report.
     */
    public function create()
    {
        $vehicles = Vehicle::all();
        $drivers = User::role('driver')->where('status', 'active')->get();
        
        if ($vehicles->isEmpty()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No vehicles found in the system.');
        }

        return view('admin.vehicle-reports.create', compact('vehicles', 'drivers'));
    }

    /**
     * Store a newly created vehicle report in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:mechanical,electrical,body_damage,tire,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high',
            'location' => 'required|string|max:255',
        ]);

        $report = VehicleReport::create([
            'status' => 'pending',
            ...$validated
        ]);

        // Update vehicle status if severity is high
        if ($validated['severity'] === 'high') {
            $vehicle = Vehicle::find($validated['vehicle_id']);
            $vehicle->update(['status' => 'maintenance']);
        }

        return redirect()
            ->route('admin.vehicle-reports.index')
            ->with('success', 'Vehicle report created successfully.');
    }

    /**
     * Display the specified vehicle report.
     */
    public function show($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            $vehicleReport->load([
                'vehicle.brand', 
                'vehicle.type.category', 
                'vehicle.maintenanceTasks' => function($query) {
                    $query->latest()->take(5);
                },
                'vehicle.serviceRequests' => function($query) {
                    $query->latest()->take(5);
                },
                'driver'
            ]);
            
            return view('admin.vehicle-reports.show', compact('vehicleReport'));
            
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'The vehicle report you are looking for has been deleted or does not exist.');
        }
    }

    /**
     * Show the form for editing the specified vehicle report.
     */
    public function edit($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            $vehicles = Vehicle::all();
            $drivers = User::role('driver')->where('status', 'active')->get();
            
            return view('admin.vehicle-reports.edit', compact('vehicleReport', 'vehicles', 'drivers'));
            
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'The vehicle report you are trying to edit has been deleted or does not exist.');
        }
    }

    /**
     * Update the specified vehicle report in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'user_id' => 'required|exists:users,id',
                'type' => 'required|in:mechanical,electrical,body_damage,tire,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'required|in:low,medium,high',
                'location' => 'required|string|max:255',
                'status' => 'required|in:pending,in_progress,resolved,cancelled'
            ]);

            $oldSeverity = $vehicleReport->severity;
            $vehicleReport->update($validated);

            // Handle vehicle status changes based on severity changes
            if ($oldSeverity === 'high' && $validated['severity'] !== 'high') {
                // Check if there are any other high severity reports for this vehicle
                $hasOtherHighSeverity = VehicleReport::where('vehicle_id', $vehicleReport->vehicle_id)
                    ->where('id', '!=', $vehicleReport->id)
                    ->where('severity', 'high')
                    ->where('status', '!=', 'resolved')
                    ->exists();
                
                if (!$hasOtherHighSeverity) {
                    $vehicleReport->vehicle->update(['status' => 'active']);
                }
            } elseif ($oldSeverity !== 'high' && $validated['severity'] === 'high') {
                $vehicleReport->vehicle->update(['status' => 'maintenance']);
            }

            return redirect()
                ->route('admin.vehicle-reports.show', $vehicleReport)
                ->with('success', 'Vehicle report updated successfully.');
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'The vehicle report you are trying to update has been deleted or does not exist.');
        }
    }

    /**
     * Remove the specified vehicle report from storage.
     */
    public function destroy($id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            // If this was a high severity report, check if there are other high severity reports
            if ($vehicleReport->severity === 'high') {
                $hasOtherHighSeverity = VehicleReport::where('vehicle_id', $vehicleReport->vehicle_id)
                    ->where('id', '!=', $vehicleReport->id)
                    ->where('severity', 'high')
                    ->where('status', '!=', 'resolved')
                    ->exists();
                
                if (!$hasOtherHighSeverity) {
                    $vehicleReport->vehicle->update(['status' => 'available']);
                }
            }

            $vehicleReport->delete();

            return redirect()
                ->route('admin.vehicle-reports.index')
                ->with('success', 'Vehicle report deleted successfully.');
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('admin.vehicle-reports.index')
                ->with('info', 'The vehicle report was already deleted.');
        }
    }

    /**
     * Update the status of a vehicle report.
     */
    /**
     * Complete a vehicle report with resolution details.
     */
    public function complete(Request $request, $id)
    {
        try {
            $report = VehicleReport::findOrFail($id);
            
            // Validate the request
            $validated = $request->validate([
                'resolution_notes' => ['required', 'string', 'max:1000'],
                'parts_used' => ['nullable', 'string', 'max:500'],
                'labor_hours' => ['required', 'numeric', 'min:0', 'max:1000'],
                'total_cost' => ['required', 'numeric', 'min:0'],
                'completion_date' => ['required', 'date'],
            ]);
            
            // Format the completion date
            $completionDate = \Carbon\Carbon::parse($validated['completion_date']);
            
            // Update the report with completion details
            $report->update([
                'status' => 'resolved',
                'resolution_notes' => $validated['resolution_notes'],
                'parts_used' => $validated['parts_used'],
                'labor_hours' => $validated['labor_hours'],
                'total_cost' => $validated['total_cost'],
                'completion_date' => $completionDate,
                'resolved_at' => $completionDate,
            ]);
            
            // Update related maintenance task if exists
            if ($report->maintenanceTask) {
                $report->maintenanceTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'notes' => $validated['resolution_notes'],
                ]);
                
                // Update the assigned staff's workload
                if ($report->maintenanceTask->assignedTo) {
                    $report->maintenanceTask->assignedTo->decrement('workload');
                }
            }
            
            // Update vehicle status if it was in maintenance
            if ($report->vehicle && $report->vehicle->status === 'maintenance') {
                // Check if there are other active maintenance tasks for this vehicle
                $hasOtherActiveMaintenance = MaintenanceTask::where('vehicle_id', $report->vehicle_id)
                    ->where('id', '!=', $report->maintenanceTask?->id)
                    ->where('status', '!=', 'completed')
                    ->exists();
                
                if (!$hasOtherActiveMaintenance) {
                    $report->vehicle->update(['status' => 'available']);
                }
            }
            
            // Send notifications
            $this->notifyStatusUpdate($report, 'in_progress');
            
            return redirect()->route('admin.vehicle-reports.show', $report->id)
                ->with('success', 'Vehicle report has been marked as completed successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete vehicle report: ' . $e->getMessage());
        }
    }
    
    /**
     * Update the status of a vehicle report.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $vehicleReport = VehicleReport::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:pending,in_progress,resolved,cancelled'
            ]);

            $oldStatus = $vehicleReport->status;
            $newStatus = $request->status;
            
            // Start a database transaction
            DB::beginTransaction();
            
            try {
                // Update the report status
                $vehicleReport->update([
                    'status' => $newStatus,
                    'resolved_at' => $newStatus === 'resolved' ? now() : null,
                ]);
                
                // If status is in_progress, assign a maintenance staff and create/update a maintenance task
                if ($newStatus === 'in_progress') {
                    // Find an available maintenance staff
                    $maintenanceStaff = User::role('maintenance_staff')
                        ->where('status', 'active')
                        ->orderBy('workload', 'asc')
                        ->first();
                        
                    if ($maintenanceStaff) {
                        // Check if a maintenance task already exists for this report
                        $task = \App\Models\MaintenanceTask::where('vehicle_report_id', $vehicleReport->id)->first();
                        
                        if (!$task) {
                            // Create a new maintenance task if one doesn't exist
                            // Set due date to 3 days from now
                            $dueDate = now()->addDays(3);
                            
                            // Create the maintenance task
                            $task = \App\Models\MaintenanceTask::create([
                                'title' => 'Repair: ' . $vehicleReport->title,
                                'description' => $vehicleReport->description,
                                'maintenance_type' => 'repair',
                                'status' => 'pending',
                                'priority' => $vehicleReport->severity === 'high' ? 'high' : 'medium',
                                'scheduled_date' => now(),
                                'estimated_hours' => 1,
                                'vehicle_id' => $vehicleReport->vehicle_id,
                                'assigned_to' => $maintenanceStaff->id,
                                'created_by' => auth()->id(),
                                'due_date' => $dueDate,
                                'vehicle_report_id' => $vehicleReport->id,
                            ]);
                            
                            // Also update the vehicle report with the due date and assigned staff
                            $vehicleReport->update([
                                'assigned_to' => $maintenanceStaff->id,
                                'due_date' => $dueDate,
                                'status' => 'in_progress'
                            ]);
                            
                            // Notification will be sent via notifyStatusUpdate
                        } else {
                            // Update existing task if it exists
                            $previousAssignedTo = $task->assigned_to;
                            
                            $task->update([
                                'status' => 'pending',
                                'assigned_to' => $maintenanceStaff->id,
                                'updated_at' => now(),
                            ]);
                            
                            // Notification will be sent via notifyStatusUpdate
                        }
                        
                        // Assign the maintenance staff to the report
                        $vehicleReport->update(['assigned_to' => $maintenanceStaff->id]);
                        
                        // Update the staff's workload if this is a new assignment
                        if (!$task->wasRecentlyCreated) {
                            // Decrement workload of previously assigned staff if different
                            if ($task->assigned_to && $task->assigned_to !== $maintenanceStaff->id) {
                                User::where('id', $task->assigned_to)->decrement('workload');
                                $maintenanceStaff->increment('workload');
                            }
                        } else {
                            $maintenanceStaff->increment('workload');
                        }
                        
                        // If it's a high severity report, update vehicle status
                        if ($vehicleReport->severity === 'high') {
                            $vehicleReport->vehicle->update(['status' => 'maintenance']);
                        }
                    }
                }
                // If resolved, update vehicle status if this was a high severity report
                elseif ($newStatus === 'resolved' && $vehicleReport->severity === 'high') {
                    $hasOtherHighSeverity = VehicleReport::where('vehicle_id', $vehicleReport->vehicle_id)
                        ->where('id', '!=', $vehicleReport->id)
                        ->where('severity', 'high')
                        ->where('status', '!=', 'resolved')
                        ->exists();
                    
                    if (!$hasOtherHighSeverity) {
                        $vehicleReport->vehicle->update(['status' => 'available']);
                    }
                    
                    // Decrement workload of assigned maintenance staff if any
                    if ($vehicleReport->assigned_to) {
                        User::where('id', $vehicleReport->assigned_to)->decrement('workload');
                    }
                }
                
                // Create activity log
                $statusText = str_replace('_', ' ', $newStatus);
                $activityMessage = "Report status changed from " . str_replace('_', ' ', $oldStatus) . " to {$statusText}";
                
                if ($newStatus === 'in_progress' && isset($maintenanceStaff)) {
                    $activityMessage .= " and assigned to " . $maintenanceStaff->name;
                }
                
                Activity::log(
                    auth()->user(),
                    $vehicleReport,
                    'vehicle_report_status_updated',
                    $activityMessage
                );
                
                // Commit the transaction
                DB::commit();
                
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                throw $e;
            }
            
            // Notify relevant parties about status update
            $this->notifyStatusUpdate($vehicleReport, $oldStatus);
            
            return redirect()
                ->route('admin.vehicle-reports.show', $vehicleReport)
                ->with('success', "Report status updated to {$statusText}.");
                
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'The vehicle report you are trying to update has been deleted or does not exist.');
        }
    }

    /**
     * Notify relevant parties about status update.
     */
    private function notifyStatusUpdate(VehicleReport $vehicleReport, string $oldStatus)
    {
        // Log the start of the notification process
        \Log::info('Starting notification process for report ID: ' . $vehicleReport->id . ', Status: ' . $vehicleReport->status);
        
        // Notify the driver
        if ($vehicleReport->driver) {
            try {
                \Log::info('Notifying driver ID: ' . $vehicleReport->driver->id);
                $vehicleReport->driver->notify(new VehicleReportStatusUpdated($vehicleReport, $oldStatus));
                \Log::info('Successfully notified driver ID: ' . $vehicleReport->driver->id);
            } catch (\Exception $e) {
                \Log::error('Failed to notify driver ID ' . $vehicleReport->driver->id . ': ' . $e->getMessage());
            }
        } else {
            \Log::warning('No driver found for report ID: ' . $vehicleReport->id);
        }
        
        // Notify the assigned maintenance staff if status is in_progress and there's an assigned staff
        if ($vehicleReport->status === 'in_progress' && $vehicleReport->assigned_to) {
            \Log::info('Preparing to notify assigned staff for report ID: ' . $vehicleReport->id . ', Assigned to: ' . $vehicleReport->assigned_to);
            
            // Eager load the assigned staff with their roles
            $assignedStaff = User::with('roles')->find($vehicleReport->assigned_to);
            
            if ($assignedStaff) {
                $roleNames = $assignedStaff->roles->pluck('name')->implode(', ');
                \Log::info('Found assigned staff - ID: ' . $assignedStaff->id . 
                          ', Name: ' . $assignedStaff->name . 
                          ', Roles: ' . $roleNames);
                
                // Check if the user has the maintenance_staff role
                $isMaintenanceStaff = $assignedStaff->hasRole('maintenance_staff');
                \Log::info('User has maintenance_staff role: ' . ($isMaintenanceStaff ? 'Yes' : 'No'));
                
                try {
                    try {
                        // Send the notification
                        $notification = new VehicleReportStatusUpdated($vehicleReport, $oldStatus);
                        $assignedStaff->notify($notification);
                        \Log::info('Successfully sent VehicleReportStatusUpdated notification to staff ID: ' . $assignedStaff->id);
                        
                        // Force save the notification to the database
                        $dbNotification = $assignedStaff->notifications()->create([
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'type' => get_class($notification),
                            'data' => json_encode($notification->toArray($assignedStaff)),
                            'read_at' => null,
                        ]);
                        
                        if ($dbNotification) {
                            \Log::info('Notification stored in database with ID: ' . $dbNotification->id);
                        } else {
                            \Log::warning('Failed to store notification in database for user ID: ' . $assignedStaff->id);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error sending notification to staff ID ' . $assignedStaff->id . ': ' . $e->getMessage());
                        \Log::error($e->getTraceAsString());
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send VehicleReportStatusUpdated notification to staff ID ' . $assignedStaff->id . ': ' . $e->getMessage());
                    \Log::error('Error trace: ' . $e->getTraceAsString());
                }
            } else {
                \Log::warning('Assigned staff not found for report ID: ' . $vehicleReport->id . ', Assigned to: ' . $vehicleReport->assigned_to);
            }
        } else {
            \Log::info('Skipping maintenance staff notification - Status: ' . $vehicleReport->status . ', Assigned to: ' . ($vehicleReport->assigned_to ?: 'None'));
        }
    }
}
