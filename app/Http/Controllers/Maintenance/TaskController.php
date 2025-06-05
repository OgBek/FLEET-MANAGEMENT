<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTask;
use App\Models\Vehicle;
use App\Models\VehicleReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:maintenance_staff']);
    }

    public function index()
    {
        $user_id = auth()->id();
        
        // Get maintenance tasks
        $tasks = MaintenanceTask::where('assigned_to', $user_id)
            ->with(['vehicle.brand', 'createdBy'])
            ->latest()
            ->get();
        
        // Get maintenance schedules and convert them to a compatible format
        $scheduledTasks = \App\Models\MaintenanceSchedule::where('assigned_to', $user_id)
            ->with(['vehicle.brand'])
            ->get()
            ->map(function($schedule) {
                // Convert MaintenanceSchedule to a format compatible with MaintenanceTask
                $task = new \stdClass();
                $task->id = 'schedule_' . $schedule->id; // Prefix to distinguish from regular tasks
                $task->title = ucfirst(str_replace('_', ' ', $schedule->maintenance_type));
                $task->description = $schedule->description;
                $task->maintenance_type = $schedule->maintenance_type;
                $task->status = $schedule->status;
                $task->priority = 'medium'; // Default priority if not specified
                $task->scheduled_date = $schedule->scheduled_date;
                
                // Handle vehicle relationship properly
                $task->vehicle = $schedule->vehicle;
                if ($task->vehicle && $task->vehicle->brand) {
                    // Ensure brand is properly formatted
                    $brandName = is_object($task->vehicle->brand) ? ($task->vehicle->brand->name ?? '') : $task->vehicle->brand;
                    $task->vehicle->formattedBrand = $brandName;
                }
                
                $task->type = 'schedule'; // Mark as a schedule for special handling in view
                $task->original_model = $schedule;
                $task->schedule_id = $schedule->id; // Store the original ID for routes
                
                return $task;
            });

        // Get service requests assigned to the maintenance staff
        $serviceRequests = \App\Models\ServiceRequest::where('assigned_to', $user_id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->with(['vehicle.brand'])
            ->get()
            ->map(function($request) {
                $task = new \stdClass();
                $task->id = 'request_' . $request->id;
                $task->title = $request->issue_title;
                $task->description = $request->issue_description;
                $task->maintenance_type = 'service_request';
                $task->status = $request->status;
                $task->priority = $request->priority;
                $task->scheduled_date = $request->scheduled_date;
                
                // Handle vehicle relationship
                $task->vehicle = $request->vehicle;
                if ($task->vehicle && $task->vehicle->brand) {
                    $brandName = is_object($task->vehicle->brand) ? ($task->vehicle->brand->name ?? '') : $task->vehicle->brand;
                    $task->vehicle->formattedBrand = $brandName;
                }
                
                $task->type = 'service_request';
                $task->original_model = $request;
                $task->request_id = $request->id;
                
                return $task;
            });
        
        // Merge all collections and paginate the result
        $allTasks = $tasks->concat($scheduledTasks)->concat($serviceRequests)->sortByDesc('scheduled_date');
        $paginatedTasks = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTasks->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 10),
            $allTasks->count(),
            10,
            null,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        
        return view('maintenance.tasks.index', [
            'tasks' => $paginatedTasks,
            'hasSchedules' => $scheduledTasks->count() > 0,
            'hasServiceRequests' => $serviceRequests->count() > 0
        ]);
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('registration_number')->get();
        $maintenanceTypes = [
            'preventive' => 'Preventive Maintenance',
            'corrective' => 'Corrective Maintenance',
            'inspection' => 'Regular Inspection',
            'repair' => 'Repair Work'
        ];

        return view('maintenance.tasks.create', compact('vehicles', 'maintenanceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'maintenance_type' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'scheduled_date' => ['required', 'date'],
            'estimated_hours' => ['required', 'numeric', 'min:0.5'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ]);

        $task = MaintenanceTask::create([
            ...$validated,
            'status' => 'pending',
            'assigned_to' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('maintenance.tasks.show', $task)
            ->with('status', 'Maintenance task created successfully.');
    }

    public function show(MaintenanceTask $task)
    {
        $task->load([
            'vehicle', 
            'createdBy', 
            'assignedStaff',
            'completedBy',
            'history.causer' // Load history with the user who caused the change
        ]);
        
        return view('maintenance.tasks.show', compact('task'));
    }
    
    public function edit(MaintenanceTask $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }
        
        if (in_array($task->status, ['completed', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot edit a task that is already ' . $task->status . '.');
        }
        
        $vehicles = Vehicle::orderBy('registration_number')->get();
        $maintenanceTypes = [
            'preventive' => 'Preventive Maintenance',
            'corrective' => 'Corrective Maintenance',
            'inspection' => 'Regular Inspection',
            'repair' => 'Repair Work'
        ];
        
        return view('maintenance.tasks.edit', compact('task', 'vehicles', 'maintenanceTypes'));
    }
    
    public function update(Request $request, MaintenanceTask $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }
        
        if (in_array($task->status, ['completed', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot update a task that is already ' . $task->status . '.');
        }
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'maintenance_type' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'scheduled_date' => ['required', 'date'],
            'estimated_hours' => ['required', 'numeric', 'min:0.5'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ]);
        
        $task->update($validated);
        
        return redirect()->route('maintenance.tasks.show', $task)
            ->with('status', 'Task updated successfully.');
    }

    public function start(MaintenanceTask $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }

        $task->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        return redirect()->route('maintenance.tasks.show', $task)
            ->with('status', 'Task has been started.');
    }

    public function complete(Request $request, MaintenanceTask $task)
    {
        // Check if the activity log is properly configured
        if (!function_exists('activity')) {
            Log::error('Activity log function is not available');
            return response()->json([
                'message' => 'Activity logging is not properly configured.',
                'errors' => []
            ], 500);
        }
        
        Log::info('Complete task request received', [
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            // Validate the request data
            $validated = $request->validate([
                'resolution_notes' => ['required', 'string', 'min:10'],
                'parts_used' => ['nullable', 'string'],
                'labor_hours' => ['required', 'numeric', 'min:0.1'],
                'total_cost' => ['required', 'numeric', 'min:0']
            ]);

            Log::debug('Validation passed', ['validated_data' => $validated]);

            DB::beginTransaction();

            // Update the task with completion details
            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => auth()->id(),
                'resolution_notes' => $validated['resolution_notes'],
                'parts_used' => $validated['parts_used'] ?? null,
                'labor_hours' => $validated['labor_hours'],
                'total_cost' => $validated['total_cost']
            ];

            Log::debug('Updating task with data', $updateData);
            $task->update($updateData);
            Log::debug('Task updated successfully');

            // Check for related vehicle reports that need to be updated
            $relatedReports = VehicleReport::where('vehicle_id', $task->vehicle_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->where(function($query) use ($task) {
                    $query->where('description', 'like', '%' . substr($task->description, 0, 30) . '%')
                          ->orWhere('title', 'like', '%' . substr($task->title, 0, 20) . '%');
                })
                ->get();
            
            Log::debug('Found related reports', ['count' => $relatedReports->count()]);
            
            // Update any found related reports
            foreach ($relatedReports as $report) {
                $report->status = 'resolved';
                $report->resolved_at = now();
                $report->resolution_notes = ($report->resolution_notes ? $report->resolution_notes . "\n\n" : "") . 
                    "Resolved via maintenance task #" . $task->id . 
                    " completed by " . auth()->user()->name . " on " . now()->format('Y-m-d H:i');
                $report->save();
                
                // Log the status change
                try {
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($report)
                        ->log('marked as resolved');
                } catch (\Exception $e) {
                    Log::error('Failed to log activity for report', [
                        'report_id' => $report->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Check if the vehicle has any other active maintenance
            $hasOtherMaintenance = DB::table('maintenance_tasks')
                ->where('vehicle_id', $task->vehicle_id)
                ->where('id', '!=', $task->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();
                
            $hasMaintenanceSchedules = DB::table('maintenance_schedules')
                ->where('vehicle_id', $task->vehicle_id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();
            
            Log::debug('Vehicle maintenance status', [
                'has_other_maintenance' => $hasOtherMaintenance,
                'has_maintenance_schedules' => $hasMaintenanceSchedules,
                'vehicle_id' => $task->vehicle_id
            ]);
                
            // Update vehicle status if no other maintenance is needed
            if (!$hasOtherMaintenance && !$hasMaintenanceSchedules && $task->vehicle) {
                $task->vehicle->update(['status' => 'available']);
                Log::debug('Vehicle status updated to available', ['vehicle_id' => $task->vehicle_id]);
            }
            
            // Log the completion
            activity()
                ->causedBy(auth()->user())
                ->performedOn($task)
                ->log('marked task as completed');

            DB::commit();
            Log::info('Task completed successfully', ['task_id' => $task->id]);

            return response()->json([
                'redirect' => route('maintenance.tasks.show', $task),
                'message' => 'Task has been marked as completed.' . 
                    ($relatedReports->count() > 0 ? ' ' . $relatedReports->count() . ' related vehicle report(s) marked as resolved.' : '')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', [
                'task_id' => $task->id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error completing task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'An error occurred while completing the task: ' . $e->getMessage()
            ], 500);
        }
    }
} 