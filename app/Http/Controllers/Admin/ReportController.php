<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        // Get quick statistics
        $stats = [
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'approved')
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->count(),
            'total_vehicles' => Vehicle::count(),
            'vehicles_in_maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'total_maintenance_cost' => MaintenanceRecord::where('status', 'completed')
                ->sum('cost'),
            'total_distance' => Booking::where('status', 'completed')
                ->sum('actual_distance')
        ];

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Generate booking reports.
     */
    public function bookings(Request $request)
    {
        try {
            $query = Booking::with(['vehicle.brand', 'department', 'requestedBy']);

            if ($request->filled('start_date')) {
                $query->where('start_time', '>=', $request->start_date . ' 00:00:00');
            }

            if ($request->filled('end_date')) {
                $query->where('end_time', '<=', $request->end_date . ' 23:59:59');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $bookings = $query->latest()->paginate(5);

            // Calculate statistics
            $stats = [
                'total_bookings' => Booking::count(),
                'approved_bookings' => Booking::where('status', 'approved')->count(),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'completed_bookings' => Booking::where('status', 'completed')->count(),
                'cancelled_bookings' => Booking::where('status', 'cancelled')->count()
            ];

            // If this is the first time loading the page (no filters applied), don't show success message
            $message = $request->hasAny(['start_date', 'end_date', 'status']) 
                ? 'Report generated successfully. You can now export the data.' 
                : null;

            return view('admin.reports.bookings', compact('bookings', 'stats'))
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate booking report: ' . $e->getMessage());
        }
    }

    /**
     * Generate maintenance reports.
     */
    public function maintenance(Request $request)
    {
        try {
            // Get maintenance records from both tables
            $recordsQuery = MaintenanceRecord::with(['vehicle.brand', 'maintenanceStaff']);
            $schedulesQuery = MaintenanceSchedule::with(['vehicle.brand', 'assignedStaff']);
            $tasksQuery = \App\Models\MaintenanceTask::with(['vehicle.brand', 'assignedTo']);
            
            // Apply filters if provided
            if ($request->filled('start_date')) {
                $recordsQuery->where('service_date', '>=', $request->start_date);
                $schedulesQuery->where('scheduled_date', '>=', $request->start_date);
                $tasksQuery->where('scheduled_date', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $recordsQuery->where('service_date', '<=', $request->end_date . ' 23:59:59');
                $schedulesQuery->where('scheduled_date', '<=', $request->end_date . ' 23:59:59');
                $tasksQuery->where('scheduled_date', '<=', $request->end_date . ' 23:59:59');
            }
            
            if ($request->filled('maintenance_type')) {
                $recordsQuery->where('service_type', $request->maintenance_type);
                $schedulesQuery->where('maintenance_type', $request->maintenance_type);
                $tasksQuery->where('maintenance_type', $request->maintenance_type);
            }

            // Get the records, schedules, and tasks
            $maintenanceRecords = $recordsQuery->get();
            $maintenanceSchedules = $schedulesQuery->get();
            $maintenanceTasks = $tasksQuery->get();
            
            // Combine the records from all sources into a collection
            $combinedRecords = collect();
            
            // Add maintenance records
            foreach ($maintenanceRecords as $record) {
                $combinedRecords->push([
                    'id' => 'record_' . $record->id,
                    'type' => 'record',
                    'vehicle' => $record->vehicle,
                    'service_type' => $record->service_type,
                    'date' => $record->service_date,
                    'status' => $record->status,
                    'staff' => $record->maintenanceStaff,
                    'cost' => $record->cost,
                    'description' => $record->description,
                    'next_service_date' => $record->next_service_date,
                    'original' => $record
                ]);
            }
            
            // Add maintenance schedules
            foreach ($maintenanceSchedules as $schedule) {
                $combinedRecords->push([
                    'id' => 'schedule_' . $schedule->id,
                    'type' => 'schedule',
                    'vehicle' => $schedule->vehicle,
                    'service_type' => $schedule->maintenance_type,
                    'date' => $schedule->scheduled_date,
                    'status' => $schedule->status,
                    'staff' => $schedule->assignedStaff,
                    'cost' => $schedule->total_cost ?? 0,
                    'description' => $schedule->description,
                    'next_service_date' => null,
                    'original' => $schedule
                ]);
            }
            
            // Add maintenance tasks
            foreach ($maintenanceTasks as $task) {
                $combinedRecords->push([
                    'id' => 'task_' . $task->id,
                    'type' => 'task',
                    'vehicle' => $task->vehicle,
                    'service_type' => $task->maintenance_type,
                    'date' => $task->scheduled_date,
                    'status' => $task->status,
                    'staff' => $task->assignedTo,
                    'cost' => $task->total_cost ?? 0,
                    'description' => $task->description,
                    'next_service_date' => $task->completed_at,
                    'original' => $task
                ]);
            }
            
            // Sort by date (newest first)
            $sortedRecords = $combinedRecords->sortByDesc('date');
            
            // Paginate the results
            $perPage = 5;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
            $currentItems = $sortedRecords->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $sortedRecords->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                    'pageName' => 'page',
                ]
            );

            // Calculate statistics including tasks
            $stats = [
                'total_records' => $combinedRecords->count(),
                'completed_records' => $combinedRecords->where('status', 'completed')->count(),
                'pending_records' => $combinedRecords->whereIn('status', ['pending', null])->count(),
                'in_progress_records' => $combinedRecords->where('status', 'in_progress')->count(),
                'total_cost' => $combinedRecords->sum('cost') ?? 0
            ];
            
            // Get unique maintenance types for the filter dropdown
            $maintenanceTypes = $combinedRecords->pluck('service_type')
                ->unique()
                ->filter()
                ->sort()
                ->values();

            // Get unique maintenance types for filter from all sources
            $maintenanceTypes = collect();
            $recordTypes = MaintenanceRecord::select('service_type')
                ->whereNotNull('service_type')
                ->distinct()
                ->pluck('service_type');
            $scheduleTypes = MaintenanceSchedule::select('maintenance_type')
                ->whereNotNull('maintenance_type')
                ->distinct()
                ->pluck('maintenance_type');
            $taskTypes = \App\Models\MaintenanceTask::select('maintenance_type')
                ->whereNotNull('maintenance_type')
                ->distinct()
                ->pluck('maintenance_type');
            
            $maintenanceTypes = $recordTypes->concat($scheduleTypes)
                ->concat($taskTypes)
                ->unique()
                ->values()
                ->filter()
                ->sort();

            // If this is the first time loading the page (no filters applied), don't show success message
            $message = $request->hasAny(['start_date', 'end_date', 'maintenance_type']) 
                ? 'Report generated successfully. You can now export the data.' 
                : null;

            return view('admin.reports.maintenance', compact(
                'paginator',
                'stats',
                'maintenanceTypes'
            ))->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate maintenance report: ' . $e->getMessage());
        }
    }

    /**
     * Generate vehicle reports.
     */
    public function vehicles(Request $request)
    {
        try {
            $query = Vehicle::with([
                'type', 
                'brand', 
                'maintenanceRecords',
                'maintenanceSchedules' => function($query) {
                    $query->where('status', '!=', 'cancelled');
                },
                'maintenanceTasks' => function($query) {
                    $query->where('status', '!=', 'cancelled');
                }
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $vehicles = $query->get()->map(function ($vehicle) {
                // Get total maintenance count from records, schedules, and tasks
                $totalMaintenance = $vehicle->maintenanceRecords()->count() + 
                                  $vehicle->maintenanceSchedules()
                                      ->where('status', '!=', 'cancelled')
                                      ->count() +
                                  $vehicle->maintenanceTasks()
                                      ->where('status', '!=', 'cancelled')
                                      ->count();

                // Get the latest maintenance date from records, schedules, or tasks
                $lastMaintenanceRecord = $vehicle->maintenanceRecords()
                    ->latest('service_date')
                    ->first();
                    
                $lastMaintenanceSchedule = $vehicle->maintenanceSchedules()
                    ->where('status', 'completed')
                    ->latest('completed_at')
                    ->first();
                    
                $lastMaintenanceTask = $vehicle->maintenanceTasks()
                    ->where('status', 'completed')
                    ->latest('completed_at')
                    ->first();

                // Compare dates to get the most recent
                $dates = [];
                if ($lastMaintenanceRecord) $dates[] = $lastMaintenanceRecord->service_date;
                if ($lastMaintenanceSchedule) $dates[] = $lastMaintenanceSchedule->completed_at;
                if ($lastMaintenanceTask) $dates[] = $lastMaintenanceTask->completed_at;
                
                $lastMaintenanceDate = !empty($dates) ? max($dates) : null;

                return [
                    'vehicle' => $vehicle,
                    'type' => $vehicle->type?->name,
                    'category' => $vehicle->type?->category?->name,
                    'total_bookings' => $vehicle->bookings()->count(),
                    'total_maintenance' => $totalMaintenance,
                    'last_maintenance' => $lastMaintenanceDate ? $lastMaintenanceDate->format('M d, Y') : null,
                    'status' => $vehicle->status
                ];
            });

            return view('admin.reports.vehicles', compact('vehicles'))
                ->with('success', 'Vehicle report generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate vehicle report: ' . $e->getMessage());
        }
    }

    /**
     * Generate department reports.
     */
    public function departments(Request $request)
    {
        try {
            $query = Department::with(['bookings', 'users']);

            if ($request->filled('department_id')) {
                $query->where('id', $request->department_id);
            }

            $departments = $query->get()->map(function ($department) {
                // Calculate total bookings for this department
                $bookings = $department->bookings;
                $completed = $bookings->where('status', 'completed')->count();
                $pending = $bookings->where('status', 'pending')->count();
                $cancelled = $bookings->where('status', 'cancelled')->count();
                $total = $bookings->count();
                
                // Calculate total users in this department
                $users = $department->users;
                $userCount = $users->count();
                
                // Calculate department booking statistics
                $averageBookingDuration = $bookings->count() > 0 
                    ? $bookings->avg(function($booking) {
                        if ($booking->start_time && $booking->end_time) {
                            return abs(Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time)));
                        }
                        return 0;
                    }) 
                    : 0;
                
                return [
                    'department' => $department,
                    'total_bookings' => $total,
                    'completed_bookings' => $completed,
                    'pending_bookings' => $pending,
                    'cancelled_bookings' => $cancelled,
                    'user_count' => $userCount,
                    'avg_booking_duration' => round($averageBookingDuration, 1)
                ];
            });

            // Get all departments for the filter dropdown
            $allDepartments = Department::all();

            return view('admin.reports.departments', compact('departments', 'allDepartments'))
                ->with('success', 'Department report generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate department report: ' . $e->getMessage());
        }
    }

    /**
     * Generate user reports.
     */
    public function users(Request $request)
    {
        try {
            $query = User::with(['department', 'roles']);

            // Filter by department
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Filter by role
            if ($request->filled('role')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            $users = $query->get()->map(function ($user) {
                // Calculate user booking statistics
                $userId = $user->id;
                $bookings = Booking::where('requested_by', $userId)
                    ->orWhere('driver_id', $userId)
                    ->orWhere('approved_by', $userId)
                    ->get();
                
                $completed = $bookings->where('status', 'completed')->count();
                $pending = $bookings->where('status', 'pending')->count();
                $cancelled = $bookings->where('status', 'cancelled')->count();
                $total = $bookings->count();
                
                // Calculate average booking duration
                $averageBookingDuration = $bookings->count() > 0 
                    ? $bookings->avg(function($booking) {
                        if ($booking->start_time && $booking->end_time) {
                            return abs(Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time)));
                        }
                        return 0;
                    }) 
                    : 0;
                
                return [
                    'user' => $user,
                    'department' => $user->department ? $user->department->name : 'N/A',
                    'role' => $user->roles->first() ? $user->roles->first()->name : 'N/A',
                    'total_bookings' => $total,
                    'completed_bookings' => $completed,
                    'pending_bookings' => $pending,
                    'cancelled_bookings' => $cancelled,
                    'avg_booking_duration' => round($averageBookingDuration, 1),
                    'last_booking' => $bookings->sortByDesc('created_at')->first() ? $bookings->sortByDesc('created_at')->first()->created_at->format('M d, Y') : 'N/A'
                ];
            });

            // Get all departments for the filter dropdown
            $departments = Department::all();
            
            // Get all roles for the filter dropdown
            $roles = Role::all();

            return view('admin.reports.users', compact('users', 'departments', 'roles'))
                ->with('success', 'User report generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate user report: ' . $e->getMessage());
        }
    }

    protected function getReportData($type, array $filters = [])
    {
        switch ($type) {
            case 'bookings':
                return $this->getBookingReportData();
            case 'maintenance':
                return $this->getMaintenanceReportData();
            case 'vehicles':
                return $this->getVehicleReportData();
            case 'departments':
                return $this->getDepartmentReportData();
            case 'users':
                return $this->getUserReportData();
            default:
                return collect();
        }
    }
    
    /**
     * Generate a summary for the report
     *
     * @param string $type
     * @param mixed $data
     * @param array $filters
     * @return array
     */
    protected function generateReportSummary($type, $data, $filters = [])
    {
        $summary = [
            'report_type' => ucwords(str_replace('_', ' ', $type)) . ' Report',
            'generated_on' => now()->format('F j, Y \a\t h:i A'),
            'generated_by' => auth()->user()->name ?? 'System',
            'total_records' => is_countable($data) ? number_format(count($data)) : '0',
        ];
        
        // Add date range if filters are provided
        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            $startDate = $filters['start_date'] ?? 'Beginning';
            $endDate = $filters['end_date'] ?? 'Now';
            $summary['date_range'] = "$startDate to $endDate";
        }
        
        // Add specific summaries based on report type
        switch ($type) {
            case 'bookings':
                if (is_countable($data) && count($data) > 0) {
                    $statusCounts = collect($data)->groupBy('status')->map->count();
                    $summary['booking_status'] = $statusCounts->map(function($count, $status) {
                        return "$status: $count";
                    })->implode(', ');
                }
                break;
                
            case 'maintenance':
                if (is_countable($data) && count($data) > 0) {
                    $statusCounts = collect($data)->groupBy('status')->map->count();
                    $summary['maintenance_status'] = $statusCounts->map(function($count, $status) {
                        return "$status: $count";
                    })->implode(', ');
                    
                    $totalCost = collect($data)->sum('cost');
                    if ($totalCost > 0) {
                        $summary['total_maintenance_cost'] = 'BIRR ' . number_format($totalCost, 2);
                    }
                }
                break;
                
            case 'vehicles':
                if (is_countable($data) && count($data) > 0) {
                    $statusCounts = collect($data)->groupBy('status')->map->count();
                    $summary['vehicle_status'] = $statusCounts->map(function($count, $status) {
                        return "$status: $count";
                    })->implode(', ');
                }
                break;
        }
        
        // Add any additional filters
        $additionalFilters = array_diff_key($filters, [
            'start_date' => '', 
            'end_date' => '', 
            '_token' => '', 
            'format' => ''
        ]);
        
        if (!empty($additionalFilters)) {
            $summary['filters'] = collect($additionalFilters)->map(function($value, $key) {
                return ucfirst($key) . ': ' . ($value ?: 'All');
            })->implode('; ');
        }
        
        return $summary;
    }

    private function getBookingReportData()
    {
        $bookings = Booking::with(['vehicle', 'department', 'requestedBy', 'driver'])
            ->get()
            ->map(function($booking) {
                return [
                    'ID' => $booking->id,
                    'Vehicle' => $booking->vehicle ? $booking->vehicle->registration_number : 'N/A',
                    'Department' => $booking->department ? $booking->department->name : 'N/A',
                    'Requester' => $booking->requestedBy ? $booking->requestedBy->name : 'N/A',
                    'Driver' => $booking->driver ? $booking->driver->name : 'N/A',
                    'Start Time' => $booking->start_time,
                    'End Time' => $booking->end_time,
                    'Status' => ucfirst($booking->status),
                    'Distance (km)' => $booking->actual_distance ?: '0',
                    'Purpose' => $booking->purpose,
                    'Created At' => $booking->created_at->format('Y-m-d H:i:s')
                ];
            });
            
        return $bookings;
    }

    private function getVehicleReportData()
    {
        return Vehicle::with(['type', 'brand', 'bookings', 'maintenanceRecords', 'vehicleReports', 'maintenanceTasks', 'maintenanceSchedules'])
            ->withSum('maintenanceRecords', 'cost')
            ->withSum(['vehicleReports' => function($query) {
                $query->whereIn('status', ['in_progress', 'resolved']);
            }], 'total_cost')
            ->withSum(['maintenanceTasks' => function($query) {
                $query->whereIn('status', ['in_progress', 'completed']);
            }], 'total_cost')
            ->withSum(['maintenanceSchedules' => function($query) {
                $query->whereIn('status', ['in_progress', 'completed']);
            }], 'total_cost')
            ->get()
            ->map(function($vehicle) {
                $totalMaintenanceCost = 
                    ($vehicle->maintenance_records_sum_cost ?? 0) + 
                    ($vehicle->vehicle_reports_sum_total_cost ?? 0) + 
                    ($vehicle->maintenance_tasks_sum_total_cost ?? 0) + 
                    ($vehicle->maintenance_schedules_sum_total_cost ?? 0);
                    
                $totalIssueReports = $vehicle->vehicleReports->whereIn('status', ['in_progress', 'resolved'])->count();
                $totalTasks = $vehicle->maintenanceTasks->whereIn('status', ['in_progress', 'completed'])->count();
                $totalSchedules = $vehicle->maintenanceSchedules->whereIn('status', ['in_progress', 'completed'])->count();
                $totalMaintenanceItems = $totalTasks + $totalSchedules + $vehicle->maintenanceRecords->count();
                
                return [
                    'ID' => $vehicle->id,
                    'Registration' => $vehicle->registration_number,
                    'Make' => $vehicle->brand ? $vehicle->brand->name : 'N/A',
                    'Model' => $vehicle->model,
                    'Year' => $vehicle->year,
                    'Type' => $vehicle->type ? $vehicle->type->name : 'N/A',
                    'Status' => ucfirst($vehicle->status),
                    'Mileage' => number_format($vehicle->current_mileage) . ' km',
                    'Total Bookings' => $vehicle->bookings_count ?? $vehicle->bookings->count(),
                    'Issue Reports' => $totalIssueReports,
                    'Maintenance Tasks' => $totalTasks,
                    'Scheduled Maintenance' => $totalMaintenanceItems,
                    'Last Maintenance' => $vehicle->last_maintenance_date ? $vehicle->last_maintenance_date->format('Y-m-d') : 'Never',
                    'Next Maintenance' => $vehicle->next_maintenance_date ? $vehicle->next_maintenance_date->format('Y-m-d') : 'Not scheduled',
                    'Total Maintenance Cost' => 'BIRR ' . number_format($totalMaintenanceCost, 2)
                ];
            });
    }

    private function getMaintenanceReportData()
    {
        // Get all maintenance records from all sources with the same filters as vehicle report
        $records = MaintenanceRecord::with(['vehicle', 'maintenanceStaff', 'vehicle.brand'])
            ->orderBy('service_date', 'desc')
            ->get();
            
        // Log record counts for debugging
        \Log::info('Maintenance Records Count: ' . $records->count());
            
        // Get maintenance schedules with the same status filters as vehicle report
        $schedules = MaintenanceSchedule::with(['vehicle', 'assignedStaff', 'vehicle.brand'])
            ->whereIn('status', ['in_progress', 'completed'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
            
        \Log::info('Maintenance Schedules Count: ' . $schedules->count());
            
        // Get maintenance tasks with the same status filters as vehicle report
        $tasks = \App\Models\MaintenanceTask::with(['vehicle', 'assignedTo', 'vehicle.brand'])
            ->whereIn('status', ['in_progress', 'completed'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
            
        \Log::info('Maintenance Tasks Count: ' . $tasks->count());
            
        // Get vehicle reports with the same status filters as vehicle report
        $vehicleReports = \App\Models\VehicleReport::with([
                'vehicle', 
                'vehicle.brand', 
                'maintenanceTask.assignedTo', 
                'user',
                'maintenanceTask.vehicle'
            ])
            ->whereIn('status', ['in_progress', 'resolved'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        \Log::info('Vehicle Reports Count: ' . $vehicleReports->count());
            
        // Load all users for staff assignment lookups
        $users = \App\Models\User::all()->keyBy('id');
        
        $data = collect();
        
        // Add maintenance records
        foreach ($records as $record) {
            $data->push([
                'ID' => 'MR' . $record->id,
                'Type' => 'Maintenance Record',
                'Vehicle' => $record->vehicle ? $record->vehicle->registration_number . ' (' . ($record->vehicle->brand ? $record->vehicle->brand->name : 'N/A') . ' ' . $record->vehicle->model . ')' : 'N/A',
                'Service Type' => $record->service_type ?: 'N/A',
                'Date' => $record->service_date ? $record->service_date->format('Y-m-d') : 'N/A',
                'Status' => ucfirst($record->status),
                'Staff' => $record->maintenanceStaff ? $record->maintenanceStaff->name : 'N/A',
                'Cost' => 'BIRR ' . number_format($record->cost, 2),
                'Description' => $record->description ?: 'N/A',
                'Next Service' => $record->next_service_date ? $record->next_service_date->format('Y-m-d') : 'N/A',
                'Source' => 'Maintenance Record'
            ]);
        }
        
        // Add maintenance schedules
        foreach ($schedules as $schedule) {
            $data->push([
                'ID' => 'MS' . $schedule->id,
                'Type' => 'Scheduled Maintenance',
                'Vehicle' => $schedule->vehicle ? $schedule->vehicle->registration_number . ' (' . ($schedule->vehicle->brand ? $schedule->vehicle->brand->name : 'N/A') . ' ' . $schedule->vehicle->model . ')' : 'N/A',
                'Service Type' => $schedule->maintenance_type ? ucfirst(str_replace('_', ' ', $schedule->maintenance_type)) : 'N/A',
                'Date' => $schedule->scheduled_date ? $schedule->scheduled_date->format('Y-m-d') : 'N/A',
                'Status' => ucfirst(str_replace('_', ' ', $schedule->status)),
                'Staff' => $schedule->assignedStaff ? $schedule->assignedStaff->name : 'N/A',
                'Cost' => $schedule->total_cost ? 'BIRR ' . number_format($schedule->total_cost, 2) : 'N/A',
                'Description' => $schedule->description ?: 'N/A',
                'Next Service' => 'N/A',
                'Source' => 'Scheduled Maintenance'
            ]);
        }
        
        // Add maintenance tasks (including those from vehicle reports)
        $processedTasks = [];
        
        // First, add standalone maintenance tasks
        foreach ($tasks as $task) {
            if (!in_array($task->id, $processedTasks)) {
                $data->push([
                    'ID' => 'MT' . $task->id,
                    'Type' => 'Maintenance Task',
                    'Vehicle' => $task->vehicle ? $task->vehicle->registration_number . ' (' . ($task->vehicle->brand ? $task->vehicle->brand->name : 'N/A') . ' ' . $task->vehicle->model . ')' : 'N/A',
                    'Service Type' => $task->maintenance_type ? ucfirst(str_replace('_', ' ', $task->maintenance_type)) : 'N/A',
                    'Date' => $task->scheduled_date ? $task->scheduled_date->format('Y-m-d') : 'N/A',
                    'Status' => ucfirst($task->status),
                    'Staff' => $task->assignedTo ? $task->assignedTo->name : 'N/A',
                    'Cost' => $task->total_cost ? 'BIRR ' . number_format($task->total_cost, 2) : 'N/A',
                    'Description' => $task->description ?: 'N/A',
                    'Next Service' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                    'Source' => 'Maintenance Task'
                ]);
                $processedTasks[] = $task->id;
            }
        }
        
        // Add vehicle issue reports and their associated maintenance tasks
        foreach ($vehicleReports as $report) {
            try {
                // Add the vehicle report itself
                $assignedTo = 'Unassigned';
                
                // Try to get assigned staff in order of preference
                if ($report->assigned_to && isset($users[$report->assigned_to])) {
                    $assignedTo = $users[$report->assigned_to]->name;
                } elseif ($report->maintenanceTask && $report->maintenanceTask->assignedTo) {
                    $assignedTo = $report->maintenanceTask->assignedTo->name;
                } elseif ($report->user) {
                    $assignedTo = $report->user->name;
                }
                
                $vehicleInfo = 'N/A';
                if ($report->vehicle) {
                    $brandName = $report->vehicle->brand ? $report->vehicle->brand->name : 'N/A';
                    $vehicleInfo = $report->vehicle->registration_number . ' (' . $brandName . ' ' . $report->vehicle->model . ')';
                }
                
                $data->push([
                    'ID' => 'VR' . $report->id,
                    'Type' => 'Vehicle Issue',
                    'Vehicle' => $vehicleInfo,
                    'Service Type' => 'Issue: ' . ($report->title ?: 'N/A'),
                    'Date' => $report->created_at ? $report->created_at->format('Y-m-d') : 'N/A',
                    'Status' => ucfirst(str_replace('_', ' ', $report->status)),
                    'Staff' => $assignedTo,
                    'Cost' => $report->total_cost ? 'BIRR ' . number_format($report->total_cost, 2) : 'N/A',
                    'Description' => $report->description ?: 'N/A',
                    'Next Service' => $report->due_date ? $report->due_date->format('Y-m-d') : 'N/A',
                    'Source' => 'Vehicle Issue Report'
                ]);
                
                // Add the associated maintenance task if it exists and hasn't been added yet
                if ($report->maintenanceTask && !in_array($report->maintenanceTask->id, $processedTasks)) {
                    $task = $report->maintenanceTask;
                    $taskVehicleInfo = 'N/A';
                    
                    if ($task->vehicle) {
                        $taskBrandName = $task->vehicle->brand ? $task->vehicle->brand->name : 'N/A';
                        $taskVehicleInfo = $task->vehicle->registration_number . ' (' . $taskBrandName . ' ' . $task->vehicle->model . ')';
                    } elseif ($report->vehicle) {
                        // Fall back to report's vehicle if task doesn't have one
                        $taskBrandName = $report->vehicle->brand ? $report->vehicle->brand->name : 'N/A';
                        $taskVehicleInfo = $report->vehicle->registration_number . ' (' . $taskBrandName . ' ' . $report->vehicle->model . ')';
                    }
                    
                    $data->push([
                        'ID' => 'MT' . $task->id,
                        'Type' => 'Maintenance Task',
                        'Vehicle' => $taskVehicleInfo,
                        'Service Type' => $task->maintenance_type ? ucfirst(str_replace('_', ' ', $task->maintenance_type)) : 'N/A',
                        'Date' => $task->scheduled_date ? $task->scheduled_date->format('Y-m-d') : 'N/A',
                        'Status' => ucfirst($task->status),
                        'Staff' => $task->assignedTo ? $task->assignedTo->name : 'N/A',
                        'Cost' => $task->total_cost ? 'BIRR ' . number_format($task->total_cost, 2) : 'N/A',
                        'Description' => $task->description ?: 'N/A',
                        'Next Service' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                        'Source' => 'Issue Maintenance Task'
                    ]);
                    $processedTasks[] = $task->id;
                }
            } catch (\Exception $e) {
                // Log any errors but continue processing other records
                \Log::error('Error processing vehicle report ' . ($report->id ?? 'unknown') . ': ' . $e->getMessage());
                continue;
            }
        }
        
        // Sort all items by date in descending order
        return $data->sortByDesc(function($item) {
            return $item['Date'];
        })->values();
    }

    private function getDepartmentReportData()
    {
        return Department::with(['users', 'bookings'])
            ->get()
            ->map(function($department) {
                $bookings = $department->bookings;
                $completed = $bookings->where('status', 'completed')->count();
                $pending = $bookings->where('status', 'pending')->count();
                $cancelled = $bookings->where('status', 'cancelled')->count();
                
                // Calculate average booking duration in hours
                $avgDuration = $bookings->filter(function($booking) {
                    return $booking->start_time && $booking->end_time;
                })->avg(function($booking) {
                    return Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time));
                });
                
                return [
                    'ID' => $department->id,
                    'Department Name' => $department->name,
                    'Code' => $department->code ?: 'N/A',
                    'Manager' => $department->head ? $department->head->name : 'Not Assigned',
                    'Total Users' => $department->users->count(),
                    'Total Bookings' => $bookings->count(),
                    'Completed Bookings' => $completed,
                    'Pending Bookings' => $pending,
                    'Cancelled Bookings' => $cancelled,
                    'Avg. Booking Duration (hours)' => $avgDuration ? round($avgDuration, 1) . ' hrs' : 'N/A',
                    'Last Booking' => $bookings->isNotEmpty() ? $bookings->sortByDesc('created_at')->first()->created_at->format('Y-m-d') : 'Never'
                ];
            });
    }

    private function getUserReportData()
    {
        try {
            // First, get all users with their department and roles
            $users = User::with(['department', 'roles'])->get();
            
            // Get all bookings grouped by user_id for better performance
            $allBookings = \App\Models\Booking::select('id', 'requested_by', 'status', 'start_time', 'end_time', 'created_at')
                ->whereIn('requested_by', $users->pluck('id'))
                ->get()
                ->groupBy('requested_by');
            
            return $users->map(function($user) use ($allBookings) {
                // Get bookings for this user
                $userBookings = $allBookings->get($user->id, collect());
                
                // Calculate booking statistics
                $completed = $userBookings->where('status', 'completed')->count();
                $pending = $userBookings->where('status', 'pending')->count();
                $cancelled = $userBookings->where('status', 'cancelled')->count();
                
                // Calculate average booking duration in hours
                $avgDuration = $userBookings->filter(function($booking) {
                    return $booking->start_time && $booking->end_time;
                })->avg(function($booking) {
                    try {
                        return Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time));
                    } catch (\Exception $e) {
                        return 0;
                    }
                });
                
                // Get last booking date
                $lastBooking = $userBookings->sortByDesc('created_at')->first();
                
                return [
                    'ID' => $user->id,
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Phone' => $user->phone ?: 'N/A',
                    'Department' => $user->department ? $user->department->name : 'N/A',
                    'Role' => $user->roles->isNotEmpty() ? $user->roles->first()->name : 'N/A',
                    'Status' => $user->is_active ? 'Active' : 'Inactive',
                    'Total Bookings' => $userBookings->count(),
                    'Completed' => $completed,
                    'Pending' => $pending,
                    'Cancelled' => $cancelled,
                    'Avg. Duration' => $avgDuration ? round($avgDuration, 1) . ' hrs' : 'N/A',
                    'Last Booking' => $lastBooking ? $lastBooking->created_at->format('Y-m-d') : 'Never',
                    'Last Login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
                    'Registered On' => $user->created_at->format('Y-m-d')
                ];
            });
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error generating user report: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Return empty collection with error information
            return collect([
                'error' => 'Failed to generate user report',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export reports in different formats.
     *
     * @param string $type The type of report to export
     * @param string $format The format to export (csv or pdf)
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request, $type)
    {
        try {
            // Get format from query parameter, default to 'csv' if not provided
            $format = strtolower($request->query('format', 'csv'));
            
            // Validate the format
            if (!in_array($format, ['csv', 'pdf'])) {
                $format = 'csv';
            }
            
            // Get the data for the report
            $data = $this->getReportData($type);
            
            if (empty($data)) {
                return back()->with('error', 'No data available for export.');
            }
            
            $now = now();
            $date = $now->format('Y-m-d');
            $filename = 'fleet_' . $type . '_report_' . $date . '.' . $format;
            
            // Get filters for the report
            $filters = $request->except(['_token', 'format']);
            
            // Generate report summary
            $summary = $this->generateReportSummary($type, $data, $filters);
            
            if ($format === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.templates.audit_report', [
                    'data' => $data,
                    'title' => ucwords(str_replace('_', ' ', $type)) . ' Report',
                    'date' => $now,
                    'filters' => $filters,
                    'summary' => $summary
                ]);
                
                // Set paper size and orientation
                $pdf->setPaper('a4', 'landscape')
                    ->setOptions([
                        'defaultFont' => 'DejaVu Sans',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'dpi' => 150,
                        'margin_top' => 20,
                        'margin_right' => 15,
                        'margin_bottom' => 25,
                        'margin_left' => 15,
                        'fontHeightRatio' => 0.9,
                        'isPhpEnabled' => true,
                    ]);
                
                return $pdf->download($filename);
            } else {
                // Generate CSV with enhanced formatting
                $headers = [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Description' => 'File Transfer',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'Expires' => '0',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Pragma' => 'public',
                ];
                
                $callback = function() use ($data, $summary, $type, $now) {
                    $file = fopen('php://output', 'w');
                    
                    // Add UTF-8 BOM for proper Excel handling
                    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
                    
                    // Add report header
                    fputcsv($file, [config('app.name', 'Fleet Management System')]);
                    fputcsv($file, [strtoupper($type) . ' REPORT']);
                    fputcsv($file, ['Generated on: ' . $now->format('F j, Y h:i A')]);
                    fputcsv($file, ['']); // Empty line
                    
                    // Add summary if available
                    if (!empty($summary)) {
                        fputcsv($file, ['REPORT SUMMARY']);
                        foreach ($summary as $key => $value) {
                            fputcsv($file, [ucwords(str_replace('_', ' ', $key)) . ':', $value]);
                        }
                        fputcsv($file, ['']); // Empty line
                    }
                    
                    // Add data headers
                    if (count($data) > 0) {
                        $firstRow = (array) $data[0];
                        // Clean up headers (remove any HTML tags)
                        $headers = array_map(function($header) {
                            return ucwords(str_replace('_', ' ', strip_tags($header)));
                        }, array_keys($firstRow));
                        
                        fputcsv($file, $headers);
                    }
                    
                    // Add data rows
                    foreach ($data as $row) {
                        $rowData = [];
                        foreach ((array) $row as $value) {
                            // Clean up each value (remove HTML tags and decode HTML entities)
                            $rowData[] = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                        fputcsv($file, $rowData);
                    }
                    
                    // Add report footer
                    fputcsv($file, ['']); // Empty line
                    fputcsv($file, ['This is a system-generated report.']);
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export report: ' . $e->getMessage());
        }
    }
    
    private function exportBookings($file)
    {
        // This method is kept for backward compatibility
        // Headers
        fputcsv($file, [
            'ID', 'Vehicle', 'Department', 'Requester', 'Driver',
            'Start Time', 'End Time', 'Status', 'Distance (km)', 'Purpose', 'Created At'
        ]);

        // Data
        $bookings = $this->getBookingReportData();
        foreach ($bookings as $booking) {
            fputcsv($file, (array) $booking);
        }
    }

    private function exportVehicles($file)
    {
        // Headers
        fputcsv($file, [
            'ID', 'Registration', 'Type', 'Status',
            'Total Bookings', 'Total Distance', 'Maintenance Cost'
        ]);

        // Data
        Vehicle::with(['type', 'bookings', 'maintenanceRecords'])
            ->chunk(100, function($vehicles) use ($file) {
                foreach($vehicles as $vehicle) {
                    fputcsv($file, [
                        $vehicle->id,
                        $vehicle->registration_number,
                        $vehicle->type ? $vehicle->type->name : 'N/A',
                        $vehicle->status,
                        $vehicle->bookings->count(),
                        $vehicle->bookings->sum('actual_distance'),
                        $vehicle->maintenanceRecords->sum('cost')
                    ]);
                }
            });
    }

    private function exportMaintenance($file)
    {
        // Headers
        fputcsv($file, [
            'ID', 'Type', 'Vehicle', 'Service Type', 'Date',
            'Status', 'Staff', 'Cost', 'Description', 'Next Service Date'
        ]);

        // Get maintenance records
        $records = MaintenanceRecord::with(['vehicle', 'maintenanceStaff'])->get();
        $schedules = MaintenanceSchedule::with(['vehicle', 'assignedStaff'])->get();
        
        // Export maintenance records
        foreach($records as $record) {
            fputcsv($file, [
                'R' . $record->id,
                'Record',
                $record->vehicle ? $record->vehicle->registration_number : 'N/A',
                $record->service_type ?: 'N/A',
                $record->service_date ? $record->service_date->format('Y-m-d') : 'N/A',
                $record->status ?: 'N/A',
                $record->maintenanceStaff ? $record->maintenanceStaff->name : 'N/A',
                $record->cost ?: 0,
                $record->description ?: 'N/A',
                $record->next_service_date ? $record->next_service_date->format('Y-m-d') : 'N/A'
            ]);
        }
        
        // Export maintenance schedules
        foreach($schedules as $schedule) {
            fputcsv($file, [
                'S' . $schedule->id,
                'Schedule',
                $schedule->vehicle ? $schedule->vehicle->registration_number : 'N/A',
                $schedule->maintenance_type ?: 'N/A',
                $schedule->scheduled_date ? $schedule->scheduled_date->format('Y-m-d') : 'N/A',
                $schedule->status ?: 'N/A',
                $schedule->assignedStaff ? $schedule->assignedStaff->name : 'N/A',
                $schedule->total_cost ?: 0,
                $schedule->description ?: 'N/A',
                'N/A'
            ]);
        }
    }

    private function exportDepartments($file)
    {
        // Headers
        fputcsv($file, [
            'ID', 'Department Name', 'Total Users', 'Total Bookings', 
            'Completed Bookings', 'Pending Bookings', 'Cancelled Bookings',
            'Avg. Booking Duration (hours)'
        ]);

        // Data
        Department::with(['bookings', 'users'])
            ->chunk(100, function($departments) use ($file) {
                foreach($departments as $department) {
                    $bookings = $department->bookings;
                    $completed = $bookings->where('status', 'completed')->count();
                    $pending = $bookings->where('status', 'pending')->count();
                    $cancelled = $bookings->where('status', 'cancelled')->count();
                    
                    $avgDuration = $bookings->count() > 0 
                        ? $bookings->avg(function($booking) {
                            if ($booking->start_time && $booking->end_time) {
                                return Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time));
                            }
                            return 0;
                        }) 
                        : 0;
                    
                    fputcsv($file, [
                        $department->id,
                        $department->name,
                        $department->users->count(),
                        $bookings->count(),
                        $completed,
                        $pending,
                        $cancelled,
                        round(abs($avgDuration), 1)
                    ]);
                }
            });
    }

    /**
     * Export users data to CSV.
     */
    private function exportUsers($file)
    {
        // Headers
        fputcsv($file, [
            'ID', 'Name', 'Email', 'Department', 'Role', 
            'Total Bookings', 'Completed Bookings', 'Pending Bookings', 
            'Cancelled Bookings', 'Avg. Booking Duration (hours)', 'Last Booking Date'
        ]);

        // Data
        User::with(['department', 'roles'])->chunk(100, function ($users) use ($file) {
            foreach ($users as $user) {
                $userId = $user->id;
                $bookings = Booking::where('requested_by', $userId)
                    ->orWhere('driver_id', $userId)
                    ->orWhere('approved_by', $userId)
                    ->get();
                
                $completed = $bookings->where('status', 'completed')->count();
                $pending = $bookings->where('status', 'pending')->count();
                $cancelled = $bookings->where('status', 'cancelled')->count();
                
                // Calculate average booking duration
                $avgDuration = 0;
                if ($bookings->count() > 0) {
                    $durations = [];
                    foreach ($bookings as $booking) {
                        if ($booking->start_time && $booking->end_time) {
                            $durations[] = abs(Carbon::parse($booking->end_time)->diffInHours(Carbon::parse($booking->start_time)));
                        }
                    }
                    if (count($durations) > 0) {
                        $avgDuration = array_sum($durations) / count($durations);
                    }
                }
                
                $lastBooking = $bookings->sortByDesc('created_at')->first();
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->department ? $user->department->name : 'N/A',
                    $user->roles->first() ? $user->roles->first()->name : 'N/A',
                    $bookings->count(),
                    $completed,
                    $pending,
                    $cancelled,
                    round($avgDuration, 1),
                    $lastBooking ? $lastBooking->created_at->format('Y-m-d') : 'N/A'
                ]);
            }
        });
    }
}