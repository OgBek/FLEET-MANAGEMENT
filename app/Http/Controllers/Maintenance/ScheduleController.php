<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:maintenance_staff']);
    }

    /**
     * Display a listing of scheduled maintenance tasks.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Determine which month to display
        $month = $request->query('month', date('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        
        // Get maintenance schedules assigned to the current user for the selected month
        $schedules = MaintenanceSchedule::with(['vehicle.brand'])
            ->where('assigned_to', $user->id)
            ->whereBetween('scheduled_date', [
                $currentMonth->copy()->startOfMonth()->startOfDay(),
                $currentMonth->copy()->endOfMonth()->endOfDay(),
            ])
            ->orderBy('scheduled_date')
            ->get();
            
        // Get service requests assigned to the current user for the selected month
        $serviceRequests = \App\Models\ServiceRequest::with(['vehicle.brand'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->whereBetween('scheduled_date', [
                $currentMonth->copy()->startOfMonth()->startOfDay(),
                $currentMonth->copy()->endOfMonth()->endOfDay(),
            ])
            ->orderBy('scheduled_date')
            ->get();
            
        // Get vehicle reports that should appear in the maintenance calendar
        $vehicleReports = \App\Models\VehicleReport::with(['vehicle.brand'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function($query) use ($user) {
                // Show reports that are either:
                // 1. Assigned to the current maintenance staff
                // 2. Pending and not yet assigned
                // 3. Have a due date within the current month
                $query->where('assigned_to', $user->id)
                      ->orWhere(function($q) {
                          $q->where('status', 'pending')
                            ->whereNull('assigned_to');
                      });
            })
            ->where(function($query) use ($currentMonth) {
                // Include reports created this month or due this month
                $query->whereBetween('created_at', [
                    $currentMonth->copy()->startOfMonth()->startOfDay(),
                    $currentMonth->copy()->endOfMonth()->endOfDay(),
                ])
                ->orWhere(function($q) use ($currentMonth) {
                    $q->whereNotNull('due_date')
                      ->whereBetween('due_date', [
                          $currentMonth->copy()->startOfMonth()->startOfDay(),
                          $currentMonth->copy()->endOfMonth()->endOfDay(),
                      ]);
                });
            })
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Get maintenance tasks for the current month (include completed tasks from the last 7 days)
        $maintenanceTasks = \App\Models\MaintenanceTask::with(['vehicle.brand', 'createdBy'])
            ->where('assigned_to', $user->id)
            ->where(function($query) use ($currentMonth) {
                // Include tasks that are pending or in progress
                $query->whereIn('status', ['pending', 'in_progress'])
                    // Or completed tasks from the last 7 days
                    ->orWhere(function($q) use ($currentMonth) {
                        $q->where('status', 'completed')
                          ->where('completed_at', '>=', now()->subDays(7));
                    });
            })
            ->whereBetween('scheduled_date', [
                $currentMonth->copy()->startOfMonth()->startOfDay(),
                $currentMonth->copy()->endOfMonth()->endOfDay(),
            ])
            ->orderBy('scheduled_date')
            ->orderBy('status') // This will group completed tasks together
            ->get();
        
        // Group schedules, service requests, vehicle reports, and maintenance tasks by date for the calendar view
        $groupedSchedules = [];
        
        // Add maintenance schedules
        foreach ($schedules as $schedule) {
            $dateKey = $schedule->scheduled_date->format('Y-m-d');
            if (!isset($groupedSchedules[$dateKey])) {
                $groupedSchedules[$dateKey] = [];
            }
            $groupedSchedules[$dateKey][] = [
                'type' => 'schedule',
                'title' => ucfirst(str_replace('_', ' ', $schedule->maintenance_type)),
                'description' => $schedule->description,
                'status' => $schedule->status,
                'vehicle' => $schedule->vehicle,
                'route' => route('maintenance.schedules.show', $schedule->id),
                'model' => $schedule
            ];
        }
        
        // Add maintenance tasks
        foreach ($maintenanceTasks as $task) {
            $dateKey = $task->scheduled_date->format('Y-m-d');
            if (!isset($groupedSchedules[$dateKey])) {
                $groupedSchedules[$dateKey] = [];
            }
            $groupedSchedules[$dateKey][] = [
                'type' => 'task',
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'vehicle' => $task->vehicle,
                'route' => route('maintenance.tasks.show', $task->id),
                'model' => $task
            ];
        }
        
        // Add service requests
        foreach ($serviceRequests as $request) {
            $dateKey = $request->scheduled_date->format('Y-m-d');
            if (!isset($groupedSchedules[$dateKey])) {
                $groupedSchedules[$dateKey] = [];
            }
            $groupedSchedules[$dateKey][] = [
                'type' => 'service_request',
                'title' => $request->issue_title,
                'description' => $request->issue_description,
                'status' => $request->status,
                'priority' => $request->priority,
                'vehicle' => $request->vehicle,
                'route' => route('maintenance.service-requests.show', $request->id),
                'model' => $request
            ];
        }
        
        // Add vehicle reports
        foreach ($vehicleReports as $report) {
            // Use due date if available, otherwise use created date
            $dateToUse = $report->due_date ?: $report->created_at;
            $dateKey = $dateToUse->format('Y-m-d');
            
            if (!isset($groupedSchedules[$dateKey])) {
                $groupedSchedules[$dateKey] = [];
            }
            
            // Determine if the report is overdue
            $isOverdue = $report->status === 'pending' && $report->due_date && now()->gt($report->due_date);
            
            // Only add if vehicle relationship exists
            if ($report->vehicle) {
                $groupedSchedules[$dateKey][] = [
                    'type' => 'vehicle_report',
                    'title' => 'Report: ' . $report->title,
                    'description' => $report->description,
                    'status' => $isOverdue ? 'overdue' : $report->status,
                    'severity' => $report->severity,
                    'vehicle' => $report->vehicle,
                    'route' => route('admin.vehicle-reports.show', $report->id),
                    'model' => $report,
                    'is_overdue' => $isOverdue,
                    'date' => $dateToUse
                ];
            }
        }
        
        return view('maintenance.schedules.index', [
            'schedules' => $schedules,
            'serviceRequests' => $serviceRequests,
            'vehicleReports' => $vehicleReports,
            'groupedSchedules' => $groupedSchedules,
            'currentMonth' => $currentMonth
        ]);
    }

    /**
     * Show the form for creating a new maintenance schedule.
     */
    public function create()
    {
        // Redirect to the service request creation page
        return redirect()->route('maintenance.service-requests.create');
    }

    /**
     * Store a newly created maintenance schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'maintenance_type' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            
            // Create the maintenance schedule
            $schedule = new MaintenanceSchedule();
            $schedule->vehicle_id = $request->vehicle_id;
            $schedule->assigned_to = auth()->id();
            $schedule->scheduled_date = $request->scheduled_date;
            $schedule->maintenance_type = $request->maintenance_type;
            $schedule->description = $request->description;
            $schedule->status = 'pending';
            $schedule->save();
            
            // Update vehicle status to maintenance
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $vehicle->status = 'maintenance';
            $vehicle->save();
            
            DB::commit();
            
            return redirect()->route('maintenance.schedules.index')
                ->with('success', 'Maintenance schedule created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating maintenance schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified maintenance schedule.
     */
    public function show(MaintenanceSchedule $schedule)
    {
        // Check if the schedule is assigned to the current user
        if ($schedule->assigned_to && $schedule->assigned_to !== auth()->id()) {
            abort(403, 'This schedule is not assigned to you.');
        }
        
        $schedule->load(['vehicle.brand']);
        
        return view('maintenance.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified maintenance schedule.
     */
    public function edit(MaintenanceSchedule $schedule)
    {
        // Check if the schedule is assigned to the current user
        if ($schedule->assigned_to && $schedule->assigned_to !== auth()->id()) {
            abort(403, 'This schedule is not assigned to you.');
        }
        
        $schedule->load(['vehicle.brand']);
        
        return view('maintenance.schedules.edit', compact('schedule'));
    }

    /**
     * Start working on a maintenance schedule.
     */
    public function start(MaintenanceSchedule $schedule)
    {
        // Check if the schedule is assigned to the current user
        if ($schedule->assigned_to && $schedule->assigned_to !== auth()->id()) {
            abort(403, 'This schedule is not assigned to you.');
        }
        
        try {
            DB::beginTransaction();
            
            // Get a fresh instance to avoid any stale data
            $schedule = MaintenanceSchedule::findOrFail($schedule->id);
            
            // Update to in_progress (using overdue status which functions as in_progress)
            $schedule->status = 'overdue';
            $schedule->save();
            
            // Update vehicle status to maintenance
            $vehicle = $schedule->vehicle;
            $vehicle->status = 'maintenance';
            $vehicle->save();
            
            // Check for related vehicle reports that need to be updated
            $relatedReports = \App\Models\VehicleReport::where('vehicle_id', $vehicle->id)
                ->whereIn('status', ['pending'])
                ->where(function($query) use ($schedule) {
                    $query->where('description', 'like', '%' . substr($schedule->description, 0, 30) . '%')
                          ->orWhere('resolution_notes', 'like', '%maintenance%' . $schedule->id . '%');
                })
                ->get();
                
            // Update any found related reports to in_progress
            foreach ($relatedReports as $report) {
                $report->status = 'in_progress';
                $report->save();
                
                // Create activity log for the status change
                \App\Models\Activity::log(
                    auth()->user(),
                    $report,
                    'maintenance',
                    'started working on vehicle report for ' . $report->vehicle->registration_number
                );
            }
            
            // Update any other reports for this vehicle that might be affected
            \App\Models\VehicleReport::where('vehicle_id', $vehicle->id)
                ->where('status', 'pending')
                ->update(['status' => 'in_progress']);
            
            DB::commit();
            
            return redirect()->route('maintenance.tasks.index')
                ->with('success', 'Maintenance task started successfully. The status is now marked as "In Progress".' . 
                       ($relatedReports->count() > 0 ? ' ' . $relatedReports->count() . ' related vehicle report(s) updated to "In Progress".' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('maintenance.tasks.index')
                ->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    /**
     * Mark a maintenance schedule as completed.
     */
    public function complete(Request $request, MaintenanceSchedule $schedule)
    {
        // Check if the schedule is assigned to the current user
        if ($schedule->assigned_to && $schedule->assigned_to !== auth()->id()) {
            abort(403, 'This schedule is not assigned to you.');
        }
        
        try {
            DB::beginTransaction();
            
            // Validate the request data
            $validated = $request->validate([
                'resolution_notes' => ['required', 'string', 'max:1000'],
                'parts_used' => ['nullable', 'string', 'max:500'],
                'labor_hours' => ['required', 'numeric', 'min:0'],
                'total_cost' => ['required', 'numeric', 'min:0']
            ]);
            
            // Get a fresh instance to avoid any stale data
            $schedule = MaintenanceSchedule::findOrFail($schedule->id);
            
            // Update schedule status and completion details
            $schedule->status = 'completed';
            $schedule->completed_at = now();
            $schedule->resolution_notes = $validated['resolution_notes'];
            $schedule->parts_used = $validated['parts_used'];
            $schedule->labor_hours = $validated['labor_hours'];
            $schedule->total_cost = $validated['total_cost'];
            $schedule->save();
            
            // Update vehicle status based on maintenance completion
            $vehicle = $schedule->vehicle;
            $hasOtherActiveMaintenance = MaintenanceSchedule::where('vehicle_id', $vehicle->id)
                ->where('id', '!=', $schedule->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();
                
            // Check for related vehicle reports that need to be updated
            $relatedReports = \App\Models\VehicleReport::where('vehicle_id', $vehicle->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->where(function($query) use ($schedule) {
                    $query->where('description', 'like', '%' . substr($schedule->description, 0, 30) . '%')
                          ->orWhere('resolution_notes', 'like', '%maintenance%' . $schedule->id . '%');
                })
                ->get();
                
            // Update any found related reports
            foreach ($relatedReports as $report) {
                $report->status = 'resolved';
                $report->resolved_at = now();
                $report->resolution_notes = ($report->resolution_notes ? $report->resolution_notes . "\n\n" : "") . 
                    "Resolved via maintenance task #" . $schedule->id . 
                    " completed by " . auth()->user()->name . " on " . now()->format('Y-m-d H:i') .
                    "\nTotal Cost: " . number_format($schedule->total_cost, 2) . " Birr";
                $report->save();
                
                // Create activity log for the status change
                \App\Models\Activity::log(
                    auth()->user(),
                    $report,
                    'maintenance',
                    'resolved vehicle report through maintenance completion for ' . $report->vehicle->registration_number
                );
            }
            
            // Update any remaining reports for this vehicle if no other maintenance is pending
            if (!$hasOtherActiveMaintenance) {
                \App\Models\VehicleReport::where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                        'resolution_notes' => DB::raw("CONCAT(COALESCE(resolution_notes, ''), '\n\nResolved automatically when all maintenance was completed.')")
                    ]);
                
                // Make vehicle available
                $vehicle->status = 'available';
                $vehicle->save();
            }
            
            DB::commit();
            
            return redirect()->route('maintenance.tasks.index')
                ->with('success', 'Maintenance task completed successfully.' . 
                    (!$hasOtherActiveMaintenance ? ' The vehicle is now available for booking.' : ' The vehicle remains in maintenance due to other scheduled tasks.') . 
                    ($relatedReports->count() > 0 ? ' ' . $relatedReports->count() . ' related vehicle report(s) marked as resolved.' : ''));
                    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('maintenance.tasks.index')
                ->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}