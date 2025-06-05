<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of service requests.
     */
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['vehicle', 'requestedBy', 'assignedTo'])
            ->orderBy('updated_at', 'desc'); // Show most recently updated first

        // Filter by status if provided, but always include completed records
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by vehicle if provided
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by assigned staff if provided
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $serviceRequests = $query->paginate(5)
            ->withQueryString();

        // Get data for filters
        $vehicles = Vehicle::all();
        $maintenanceStaff = User::role('maintenance_staff')->get();

        // Get request statistics including completed ones
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::where('status', 'pending')->count(),
            'completed_requests' => ServiceRequest::where('status', 'completed')->count(),
            'urgent_requests' => ServiceRequest::where('priority', 'urgent')
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count(),
            'this_month_requests' => ServiceRequest::whereMonth('created_at', now()->month)->count(),
            'avg_completion_time' => round(ServiceRequest::where('status', 'completed')
                ->whereNotNull('completed_at')
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, completed_at)'))) ?? 0
        ];

        return view('admin.service-requests.index', compact(
            'serviceRequests',
            'vehicles',
            'maintenanceStaff',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new service request.
     */
    public function create()
    {
        $vehicles = Vehicle::all();
        $maintenanceStaff = User::role('maintenance_staff')->get();
        $requestTypes = [
            'repair',
            'inspection',
            'maintenance',
            'breakdown',
            'other'
        ];
        $priorities = [
            'low',
            'medium',
            'high',
            'urgent'
        ];

        return view('admin.service-requests.create', compact(
            'vehicles',
            'maintenanceStaff',
            'requestTypes',
            'priorities'
        ));
    }

    /**
     * Store a newly created service request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'request_type' => ['required', 'string'],
            'issue_title' => ['required', 'string', 'max:255'],
            'issue_description' => ['required', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        // Create the service request
        $serviceRequest = ServiceRequest::create([
            'vehicle_id' => $validated['vehicle_id'],
            'requested_by' => auth()->id(),
            'assigned_to' => $validated['assigned_to'],
            'request_type' => $validated['request_type'],
            'issue_title' => $validated['issue_title'],
            'issue_description' => $validated['issue_description'],
            'additional_notes' => $validated['additional_notes'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['assigned_to'] ? 'approved' : 'pending',
            'scheduled_date' => $validated['scheduled_date'],
        ]);

        // Update vehicle status if request is urgent
        if ($validated['priority'] === 'urgent') {
            Vehicle::find($validated['vehicle_id'])->update(['status' => 'maintenance']);
        }

        return redirect()->route('admin.service-requests.index')
            ->with('success', 'Service request created successfully.');
    }

    /**
     * Display the specified service request.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        // Load relationships
        $serviceRequest->load(['vehicle', 'requestedBy', 'assignedTo']);

        // Get related service requests for the same vehicle
        $relatedRequests = ServiceRequest::where('vehicle_id', $serviceRequest->vehicle_id)
            ->where('id', '!=', $serviceRequest->id)
            ->latest()
            ->take(5)
            ->get();

        // Get maintenance staff for approval form
        $maintenanceStaff = User::role('maintenance_staff')->get();

        return view('admin.service-requests.show', compact(
            'serviceRequest',
            'relatedRequests',
            'maintenanceStaff'
        ));
    }

    /**
     * Show the form for editing the specified service request.
     */
    public function edit(ServiceRequest $serviceRequest)
    {
        // Don't allow editing of completed requests
        if ($serviceRequest->status === 'completed') {
            return back()->with('error', 'Completed service requests cannot be edited.');
        }

        $vehicles = Vehicle::all();
        $maintenanceStaff = User::whereHas('role', function($query) {
            $query->where('name', 'maintenance_staff');
        })->get();
        $requestTypes = [
            'repair',
            'inspection',
            'maintenance',
            'breakdown',
            'other'
        ];
        $priorities = [
            'low',
            'medium',
            'high',
            'urgent'
        ];

        return view('admin.service-requests.edit', compact(
            'serviceRequest',
            'vehicles',
            'maintenanceStaff',
            'requestTypes',
            'priorities'
        ));
    }

    /**
     * Update the specified service request.
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        // Don't allow updating of completed requests
        if ($serviceRequest->status === 'completed') {
            return back()->with('error', 'Completed service requests cannot be updated.');
        }

        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'request_type' => ['required', 'string'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:pending,approved,in_progress,completed,rejected'],
            'resolution_notes' => ['nullable', 'required_if:status,completed,rejected', 'string'],
        ]);

        $serviceRequest->update($request->all());

        // Update completion date if status is completed
        if ($request->status === 'completed' && !$serviceRequest->completed_at) {
            $serviceRequest->update(['completed_at' => now()]);
            
            // Update vehicle status if no other active requests
            $activeRequests = ServiceRequest::where('vehicle_id', $request->vehicle_id)
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count();
            
            if ($activeRequests === 0) {
                Vehicle::find($request->vehicle_id)->update(['status' => 'available']);
            }
        }

        return redirect()->route('admin.service-requests.index')
            ->with('status', 'Service request updated successfully.');
    }

    /**
     * Remove the specified service request.
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        // Don't allow deletion of completed or in-progress requests
        if (in_array($serviceRequest->status, ['completed', 'in_progress'])) {
            return back()->with('error', 'Completed or in-progress service requests cannot be deleted.');
        }

        $serviceRequest->delete();

        return redirect()->route('admin.service-requests.index')
            ->with('status', 'Service request deleted successfully.');
    }

    /**
     * Approve the specified service request.
     */
    public function approve(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string']
        ]);

        if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending service requests can be approved.');
        }

        // Find the maintenance staff user
        $maintenanceStaff = User::findOrFail($request->assigned_to);
        
        // Approve the service request using the model method
        $serviceRequest->approve(auth()->user(), $maintenanceStaff, $request->admin_notes);

        // Update vehicle status to maintenance
        $serviceRequest->vehicle->update(['status' => 'maintenance']);

        return redirect()->route('admin.service-requests.index')
            ->with('status', 'Service request approved successfully and assigned to maintenance staff.');
    }

    /**
     * Reject the specified service request.
     */
    public function reject(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending service requests can be rejected.');
        }

        // Reject the service request using the model method
        $serviceRequest->reject(auth()->user(), $request->rejection_reason);

        return redirect()->route('admin.service-requests.index')
            ->with('status', 'Service request rejected successfully.');
    }
} 