<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Activity;
use App\Notifications\MaintenanceTaskAssigned;
use App\Traits\NotifiesMaintenanceStaff;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceScheduleController extends Controller
{
    use NotifiesMaintenanceStaff;

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of maintenance schedules.
     */
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with(['vehicle', 'assignedStaff']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by vehicle if provided
        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by assigned staff if provided
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('scheduled_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('scheduled_date', '<=', $request->end_date);
        }

        // Order by scheduled date (most recent first)
        $query->orderBy('created_at', 'desc');

        // Get the schedules with pagination
        $maintenanceSchedules = $query->paginate(10)->withQueryString();
        
        // Get vehicle reports that are in progress with their related maintenance tasks
        $vehicleReports = \App\Models\VehicleReport::with(['vehicle', 'maintenanceTask.assignedStaff'])
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Combine schedules and vehicle reports into a single collection
        $allMaintenance = collect();
        
        // Add maintenance schedules
        foreach ($maintenanceSchedules as $schedule) {
            $allMaintenance->push([
                'id' => 'schedule_' . $schedule->id,
                'type' => 'schedule',
                'title' => ucfirst(str_replace('_', ' ', $schedule->maintenance_type)) . ' - ' . $schedule->vehicle->registration_number,
                'description' => $schedule->description,
                'status' => $schedule->status,
                'scheduled_date' => $schedule->scheduled_date,
                'assigned_to' => $schedule->assignedStaff ? $schedule->assignedStaff->name : 'Unassigned',
                'vehicle' => $schedule->vehicle,
                'created_at' => $schedule->created_at,
                'is_overdue' => $schedule->status === 'pending' && $schedule->scheduled_date < now(),
            ]);
        }
        
        // Add vehicle reports with their maintenance tasks
        foreach ($vehicleReports as $report) {
            $maintenanceTask = $report->maintenanceTask;
            $assignedTo = 'Unassigned';
            
            if ($maintenanceTask && $maintenanceTask->assignedStaff) {
                $assignedTo = $maintenanceTask->assignedStaff->name;
            } elseif ($report->assigned_to) {
                // Fallback to the assigned_to field if maintenance task is not available
                $assignedTo = User::find($report->assigned_to)->name ?? 'Unassigned';
            }
            
            $allMaintenance->push([
                'id' => 'report_' . $report->id,
                'type' => 'vehicle_report',
                'title' => 'Repair - ' . $report->vehicle->registration_number,
                'description' => $report->title . ': ' . $report->description,
                'status' => $report->status,
                'scheduled_date' => $report->created_at,
                'assigned_to' => $assignedTo,
                'vehicle' => $report->vehicle,
                'created_at' => $report->created_at,
                'is_overdue' => false,
            ]);
        }
        
        // Sort by created_at in descending order
        $allMaintenance = $allMaintenance->sortByDesc('created_at');
        
        // Get vehicles for filter
        $vehicles = Vehicle::orderBy('registration_number')->get();
        
        // Get maintenance staff for filter
        $maintenanceStaff = User::role('maintenance_staff')->orderBy('name')->get();
        
        // Get schedule statistics
        $stats = [
            'total_schedules' => $allMaintenance->count(),
            'pending_schedules' => $allMaintenance->where('status', 'pending')->count(),
            'in_progress_schedules' => $allMaintenance->where('status', 'in_progress')->count(),
            'completed_schedules' => $allMaintenance->where('status', 'completed')->count(),
            'overdue_schedules' => $allMaintenance->where('is_overdue', true)->count(),
            'this_month_schedules' => $allMaintenance->where('created_at', '>=', now()->startOfMonth())->count(),
            'vehicle_reports' => $vehicleReports->count(),
        ];

        return view('admin.maintenance-schedules.index', [
            'maintenanceSchedules' => $allMaintenance,
            'pagination' => $maintenanceSchedules,
            'vehicles' => $vehicles,
            'maintenanceStaff' => $maintenanceStaff,
            'stats' => $stats,
            'vehicleReports' => $vehicleReports
        ]);
    }

    /**
     * Show the form for creating a new maintenance schedule.
     */
    public function create()
    {
        $vehicles = Vehicle::all();
        $maintenanceStaff = User::role('maintenance_staff')->get();
        $maintenanceTypes = MaintenanceSchedule::getValidMaintenanceTypes();

        return view('admin.maintenance-schedules.create', compact(
            'vehicles',
            'maintenanceStaff',
            'maintenanceTypes'
        ));
    }

    /**
     * Store a newly created maintenance schedule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'in:' . implode(',', MaintenanceSchedule::getValidMaintenanceTypes())],
            'description' => ['required', 'string'],
            'scheduled_date' => ['required', 'date', 'after:today'],
            'mileage_interval' => ['nullable', 'integer', 'min:0'],
            'time_interval_days' => ['nullable', 'integer', 'min:0'],
            'assigned_to' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check for duplicate maintenance schedule
        $existingSchedule = MaintenanceSchedule::where('vehicle_id', $request->vehicle_id)
            ->where('maintenance_type', $request->maintenance_type)
            ->where('scheduled_date', $request->scheduled_date)
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existingSchedule) {
            return back()
                ->withInput()
                ->with('error', 'A maintenance schedule already exists for this vehicle with the same type and date.');
        }

        // Create the maintenance schedule
        $schedule = MaintenanceSchedule::create([
            'vehicle_id' => $request->vehicle_id,
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'mileage_interval' => $request->mileage_interval,
            'time_interval_days' => $request->time_interval_days,
            'status' => 'pending',
            'assigned_to' => $request->assigned_to,
            'notes' => $request->notes,
        ]);

        // Create activity log
        Activity::log(
            auth()->user(),
            $schedule,
            'maintenance',
            'scheduled maintenance for ' . $schedule->vehicle->registration_number
        );

        // Notify the assigned staff member about the new schedule
        $assignedStaff = User::find($schedule->assigned_to);
        if ($assignedStaff) {
            $this->notifyMaintenanceStaff(new MaintenanceTaskAssigned($schedule), $assignedStaff);
        }

        return redirect()->route('admin.maintenance-schedules.index')
            ->with('success', 'Maintenance schedule created successfully.');
    }

    /**
     * Display the specified maintenance schedule.
     */
    public function show(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load(['vehicle', 'assignedStaff']);

        // Get vehicle's maintenance history
        $maintenanceHistory = $maintenanceSchedule->vehicle
            ->maintenanceRecords()
            ->latest()
            ->take(5)
            ->get();

        // Get other pending schedules for the same vehicle
        $relatedSchedules = MaintenanceSchedule::where('vehicle_id', $maintenanceSchedule->vehicle_id)
            ->where('id', '!=', $maintenanceSchedule->id)
            ->where('status', 'pending')
            ->orderBy('scheduled_date')
            ->get();

        return view('admin.maintenance-schedules.show', compact(
            'maintenanceSchedule',
            'maintenanceHistory',
            'relatedSchedules'
        ));
    }

    /**
     * Show the form for editing the specified maintenance schedule.
     */
    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        // Don't allow editing of completed schedules
        if ($maintenanceSchedule->status === 'completed') {
            return back()->with('error', 'Completed maintenance schedules cannot be edited.');
        }

        $vehicles = Vehicle::all();
        $maintenanceStaff = User::role('maintenance_staff')->get();
        $maintenanceTypes = MaintenanceSchedule::getValidMaintenanceTypes();

        return view('admin.maintenance-schedules.edit', compact(
            'maintenanceSchedule',
            'vehicles',
            'maintenanceStaff',
            'maintenanceTypes'
        ));
    }

    /**
     * Update the specified maintenance schedule.
     */
    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'in:' . implode(',', MaintenanceSchedule::getValidMaintenanceTypes())],
            'description' => ['required', 'string'],
            'scheduled_date' => ['required', 'date'],
            'mileage_interval' => ['nullable', 'integer', 'min:0'],
            'time_interval_days' => ['nullable', 'integer', 'min:0'],
            'assigned_to' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled,overdue']
        ]);

        $oldAssignedTo = $maintenanceSchedule->assigned_to;
        $oldStatus = $maintenanceSchedule->status;

        // Update the schedule
        $maintenanceSchedule->update($validated);

        // Create activity log
        Activity::log(
            auth()->user(),
            $maintenanceSchedule,
            'maintenance',
            'updated maintenance schedule for ' . $maintenanceSchedule->vehicle->registration_number
        );

        // If assigned staff changed, notify the new staff member
        if ($oldAssignedTo !== $validated['assigned_to']) {
            $newStaff = User::findOrFail($validated['assigned_to']);
            $this->notifyMaintenanceStaff(new MaintenanceTaskAssigned($maintenanceSchedule), $newStaff);
        }

        // If status changed to in_progress, update vehicle status
        if ($oldStatus !== 'in_progress' && $validated['status'] === 'in_progress') {
            $maintenanceSchedule->vehicle->update(['status' => 'maintenance']);
        }
        // If status changed to completed, check if vehicle can be made available
        elseif ($oldStatus !== 'completed' && $validated['status'] === 'completed') {
            // Check if there are any other pending/in_progress maintenance schedules
            $hasOtherMaintenance = MaintenanceSchedule::where('vehicle_id', $maintenanceSchedule->vehicle_id)
                ->where('id', '!=', $maintenanceSchedule->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();

            if (!$hasOtherMaintenance) {
                $maintenanceSchedule->vehicle->update(['status' => 'available']);
            }
        }

        return redirect()->route('admin.maintenance-schedules.show', $maintenanceSchedule)
            ->with('success', 'Maintenance schedule updated successfully.');
    }

    /**
     * Start work on a maintenance schedule
     */
    public function startWork(MaintenanceSchedule $maintenanceSchedule)
    {
        try {
            if ($maintenanceSchedule->status !== 'pending') {
                throw new \Exception('Only pending maintenance schedules can be started.');
            }

            $maintenanceSchedule->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);

            // Mark vehicle as under maintenance
            $maintenanceSchedule->vehicle->markUnderMaintenance();

            // Create activity log
            Activity::log(
                auth()->user(),
                $maintenanceSchedule,
                'maintenance',
                'started work on maintenance schedule for ' . $maintenanceSchedule->vehicle->registration_number
            );

            return redirect()->route('admin.maintenance-schedules.show', $maintenanceSchedule)
                ->with('success', 'Maintenance work has been started.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete a maintenance schedule
     */
    public function complete(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        try {
            if ($maintenanceSchedule->status !== 'in_progress') {
                throw new \Exception('Only in-progress maintenance schedules can be completed.');
            }

            $validated = $request->validate([
                'completion_notes' => ['required', 'string'],
                'parts_used' => ['nullable', 'string'],
                'labor_hours' => ['required', 'numeric', 'min:0'],
                'total_cost' => ['required', 'numeric', 'min:0']
            ]);

            $maintenanceSchedule->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $validated['completion_notes'],
                'parts_used' => $validated['parts_used'],
                'labor_hours' => $validated['labor_hours'],
                'total_cost' => $validated['total_cost']
            ]);

            // Mark vehicle as available
            $maintenanceSchedule->vehicle->markAvailable();

            // Create activity log
            Activity::log(
                auth()->user(),
                $maintenanceSchedule,
                'maintenance',
                'completed maintenance schedule for ' . $maintenanceSchedule->vehicle->registration_number
            );

            return redirect()->route('admin.maintenance-schedules.show', $maintenanceSchedule)
                ->with('success', 'Maintenance schedule has been completed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified maintenance schedule.
     */
    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        // Don't allow deletion of completed schedules
        if ($maintenanceSchedule->status === 'completed') {
            return back()->with('error', 'Completed maintenance schedules cannot be deleted.');
        }

        $maintenanceSchedule->delete();

        return redirect()->route('admin.maintenance-schedules.index')
            ->with('status', 'Maintenance schedule deleted successfully.');
    }

    /**
     * Redirect from old maintenance routes to new maintenance-schedules routes
     */
    public function redirectToSchedules()
    {
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Display maintenance schedule form
     */
    public function schedule(Request $request)
    {
        $vehicles = Vehicle::all();
        $mechanics = User::role('maintenance_staff')->orderBy('name')->get();
        $maintenanceTypes = MaintenanceSchedule::getValidMaintenanceTypes();
        
        // Check for overdue schedules
        MaintenanceSchedule::where('status', 'pending')
            ->where('scheduled_date', '<', now())
            ->update(['status' => 'overdue']);
            
        // Get maintenance schedules with pagination
        $query = MaintenanceSchedule::with(['vehicle', 'assignedStaff']);
        
        // Apply filters if provided
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        if ($request->filled('mechanic_id')) {
            $query->where('assigned_to', $request->mechanic_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }
        
        // Order by scheduled date
        $query->orderBy('scheduled_date');
        
        // Get paginated results
        $schedules = $query->paginate(10)->withQueryString();
            
        return view('admin.maintenance.schedule', compact('vehicles', 'maintenanceTypes', 'schedules', 'mechanics'));
    }

    /**
     * Redirect old maintenance edit routes to new ones
     */
    public function editRedirect($id)
    {
        $maintenanceSchedule = MaintenanceSchedule::findOrFail($id);
        return redirect()->route('admin.maintenance-schedules.edit', $maintenanceSchedule);
    }
} 