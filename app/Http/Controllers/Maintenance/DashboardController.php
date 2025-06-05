<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use App\Models\VehicleReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:maintenance_staff']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get assigned maintenance schedules
        $schedules = MaintenanceSchedule::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('scheduled_date')
            ->take(5)
            ->get();
            
        // Get assigned service requests - include both approved and in_progress
        $serviceRequests = ServiceRequest::where('assigned_to', $user->id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_date')
            ->take(5)
            ->get();
            
        // Get all vehicle reports
        $vehicleReports = VehicleReport::where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get maintenance tasks
        $maintenanceTasks = \App\Models\MaintenanceTask::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get task statistics
        $stats = [
            'pending_tasks' => MaintenanceSchedule::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count() +
                \App\Models\MaintenanceTask::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            'overdue_tasks' => MaintenanceSchedule::where('assigned_to', $user->id)
                ->where('status', 'overdue')
                ->count(),
            'pending_service_requests' => ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'approved')
                ->count(),
            'in_progress_service_requests' => ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'completed_tasks' => MaintenanceSchedule::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count() + 
                ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count() +
                \App\Models\MaintenanceTask::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count(),
            'in_progress_tasks' => MaintenanceSchedule::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count() + 
                ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count() +
                \App\Models\MaintenanceTask::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'urgent_requests' => ServiceRequest::where('assigned_to', $user->id)
                ->where('priority', 'high')
                ->whereIn('status', ['approved', 'in_progress'])
                ->count() +
                \App\Models\MaintenanceTask::where('assigned_to', $user->id)
                ->where('priority', 'high')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'vehicles_in_maintenance' => Vehicle::where('status', 'maintenance')->count()
        ];
            
        // Combine maintenance schedules and service requests to create a unified task list
        $tasks = collect();
        
        foreach ($schedules as $schedule) {
            $tasks->push((object)[
                'id' => 'schedule_' . $schedule->id,
                'title' => $schedule->maintenance_type,
                'description' => $schedule->description,
                'status' => $schedule->status,
                'scheduled_date' => $schedule->scheduled_date,
                'priority' => 'medium', // Default priority
                'type' => 'schedule',
                'schedule_id' => $schedule->id,
                'vehicle' => $schedule->vehicle,
                'maintenance_type' => $schedule->maintenance_type
            ]);
        }
        
        foreach ($serviceRequests as $request) {
            $tasks->push((object)[
                'id' => 'request_' . $request->id,
                'title' => $request->issue_title,
                'description' => $request->issue_description,
                'status' => $request->status === 'approved' ? 'pending' : $request->status,
                'scheduled_date' => $request->scheduled_date,
                'priority' => $request->priority,
                'type' => 'service_request',
                'request_id' => $request->id,
                'vehicle' => $request->vehicle,
                'maintenance_type' => $request->issue_title
            ]);
        }
        
        // Sort tasks by due date
        $tasks = $tasks->sortBy('scheduled_date');
        
        // Add maintenance tasks to the tasks list
        foreach ($maintenanceTasks as $task) {
            $tasks->push((object)[
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'scheduled_date' => $task->scheduled_date,
                'priority' => $task->priority,
                'type' => 'task',
                'task_id' => $task->id,
                'vehicle' => $task->vehicle,
                'maintenance_type' => $task->maintenance_type ?? 'maintenance'
            ]);
        }

        return view('maintenance.dashboard', compact(
            'schedules',
            'serviceRequests',
            'vehicleReports',
            'stats',
            'tasks',
            'maintenanceTasks'
        ));
    }
}
