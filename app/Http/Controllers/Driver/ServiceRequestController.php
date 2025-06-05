<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use App\Models\User;
use App\Notifications\ServiceRequest as ServiceRequestNotification;
use App\Notifications\NewIssueReport;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    /**
     * Display a listing of service requests for the driver's vehicles.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get vehicles assigned to this driver
        $assignedVehicles = Vehicle::where('driver_id', $user->id)->pluck('id');

        $query = ServiceRequest::whereIn('vehicle_id', $assignedVehicles)
            ->with(['vehicle', 'requestedBy', 'assignedTo'])
            ->orderBy('updated_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $serviceRequests = $query->paginate(10);

        // Get statistics for this driver's vehicles
        $stats = [
            'total_requests' => ServiceRequest::whereIn('vehicle_id', $assignedVehicles)->count(),
            'active_requests' => ServiceRequest::whereIn('vehicle_id', $assignedVehicles)
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count(),
            'completed_requests' => ServiceRequest::whereIn('vehicle_id', $assignedVehicles)
                ->where('status', 'completed')
                ->count(),
            'urgent_requests' => ServiceRequest::whereIn('vehicle_id', $assignedVehicles)
                ->where('priority', 'urgent')
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count()
        ];

        return view('driver.service-requests.index', compact('serviceRequests', 'stats'));
    }

    /**
     * Display the specified service request.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $user = auth()->user();
        
        // Check if this service request belongs to one of the driver's vehicles
        if (!$user->vehicles()->where('id', $serviceRequest->vehicle_id)->exists()) {
            abort(403, 'You are not authorized to view this service request.');
        }

        return view('driver.service-requests.show', compact('serviceRequest'));
    }

    /**
     * Create a new service request for the driver's vehicle.
     */
    public function create()
    {
        $user = auth()->user();
        $vehicles = $user->vehicles;
        
        return view('driver.service-requests.create', compact('vehicles'));
    }

    /**
     * Store a newly created service request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Validate that the vehicle belongs to this driver
        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'issue_title' => ['required', 'string', 'max:255'],
            'issue_description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent']
        ]);

        if (!$user->vehicles()->where('id', $request->vehicle_id)->exists()) {
            abort(403, 'You are not authorized to create service requests for this vehicle.');
        }

        // Create the service request
        $serviceRequest = ServiceRequest::create([
            'vehicle_id' => $request->vehicle_id,
            'issue_title' => $request->issue_title,
            'issue_description' => $request->issue_description,
            'priority' => $request->priority,
            'status' => 'pending',
            'requested_by' => $user->id,
            'scheduled_date' => now()
        ]);

        // Notify maintenance staff about the new service request
        $maintenanceStaff = User::role('maintenance_staff')->get();
        foreach ($maintenanceStaff as $staff) {
            $staff->notify(new ServiceRequestNotification($serviceRequest));
        }

        // Notify admins about the new service request with the specialized notification
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewIssueReport($serviceRequest));
        }

        return redirect()->route('driver.service-requests.show', $serviceRequest)
            ->with('success', 'Service request has been submitted successfully.');
    }
}
