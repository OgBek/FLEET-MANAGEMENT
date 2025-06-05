<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:maintenance_staff']);
    }

    public function index()
    {
        $user = auth()->user();

        // Calculate statistics
        $stats = [
            'pending' => ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'completed_today' => ServiceRequest::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'urgent' => ServiceRequest::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->where('priority', 'urgent')
                ->count(),
        ];

        // Get all vehicles for the filter dropdown
        $vehicles = Vehicle::orderBy('registration_number')->get();

        // Get service requests with filters
        $requests = ServiceRequest::where('assigned_to', $user->id)
            ->when(request('status'), function($query) {
                return $query->where('status', request('status'));
            })
            ->when(request('priority'), function($query) {
                return $query->where('priority', request('priority'));
            })
            ->when(request('vehicle_id'), function($query) {
                return $query->where('vehicle_id', request('vehicle_id'));
            })
            ->when(request('date'), function($query) {
                return $query->whereDate('scheduled_date', request('date'));
            })
            ->with(['vehicle', 'requestedBy.department'])
            ->latest()
            ->paginate(5);

        return view('maintenance.service-requests.index', compact('requests', 'stats', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::with(['brand'])
            ->orderBy('registration_number')
            ->get();
        return view('maintenance.service-requests.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issue_title' => ['required', 'string', 'max:255'],
            'issue_description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'scheduled_date' => ['required', 'date', 'after:now'],
            'additional_notes' => ['nullable', 'string']
        ]);

        $data = [
            'issue_title' => $validated['issue_title'],
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'vehicle_id' => $validated['vehicle_id'],
            'scheduled_date' => $validated['scheduled_date'],
            'status' => 'pending',  // Always start with pending status for admin approval
            'requested_by' => auth()->id(),
            // Leave assigned_to null until approved by admin
        ];
        
        if (isset($validated['additional_notes']) && !empty($validated['additional_notes'])) {
            $data['resolution_notes'] = $validated['additional_notes'];
        }

        $serviceRequest = ServiceRequest::create($data);

        // Notify admins about the new service request
        $serviceRequest->notifyAdmins();

        return redirect()->route('maintenance.service-requests.show', $serviceRequest)
            ->with('status', 'Service request has been submitted and is pending approval.');
    }

    public function show(ServiceRequest $serviceRequest)
    {
        // Allow viewing if this staff created the request or if it's assigned to them
        if ($serviceRequest->requested_by !== auth()->id() && $serviceRequest->assigned_to !== auth()->id()) {
            abort(403, 'You are not authorized to view this service request.');
        }

        // Eager load relationships
        $serviceRequest->load(['vehicle', 'requestedBy.department', 'assignedTo', 'maintenanceTasks']);

        return view('maintenance.service-requests.show', compact('serviceRequest'));
    }

    public function startWork(ServiceRequest $serviceRequest)
    {
        try {
            if ($serviceRequest->assigned_to !== auth()->id()) {
                abort(403, 'You are not authorized to start work on this service request.');
            }

            if (!$serviceRequest->isApproved()) {
                abort(403, 'Only approved service requests can be started.');
            }

            // Start the work and mark vehicle under maintenance
            $serviceRequest->startWork();

            // Notify the requester that work has started
            $serviceRequest->requestedBy->notify(new \App\Notifications\ServiceRequestStatusUpdated($serviceRequest));

            // Notify admins about work starting
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ServiceRequestStatusUpdated($serviceRequest));
            }

            return redirect()->route('maintenance.service-requests.show', $serviceRequest)
                ->with('success', 'Service request has been marked as in progress.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to start work on service request. ' . $e->getMessage());
        }
    }

    public function complete(Request $request, ServiceRequest $serviceRequest)
    {
        try {
            if ($serviceRequest->assigned_to !== auth()->id()) {
                abort(403, 'You are not authorized to complete this service request.');
            }

            if (!$serviceRequest->isInProgress()) {
                abort(403, 'Only in-progress service requests can be completed.');
            }

            $validated = $request->validate([
                'resolution_notes' => ['required', 'string', 'max:1000'],
                'parts_used' => ['nullable', 'string', 'max:500'],
                'labor_hours' => ['required', 'numeric', 'min:0'],
                'total_cost' => ['required', 'numeric', 'min:0']
            ]);

            // Complete the service request and handle vehicle status
            $serviceRequest->complete($validated);

            // Notify the requester that work is completed
            $serviceRequest->requestedBy->notify(new \App\Notifications\ServiceRequestCompleted($serviceRequest));

            // Notify admins about completion
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ServiceRequestCompleted($serviceRequest));
            }

            // If this was a vehicle maintenance request, notify department heads
            if ($serviceRequest->vehicle && $serviceRequest->vehicle->department) {
                $departmentHead = \App\Models\User::role('department_head')
                    ->where('department_id', $serviceRequest->vehicle->department_id)
                    ->first();
                
                if ($departmentHead) {
                    $departmentHead->notify(new \App\Notifications\ServiceRequestCompleted($serviceRequest));
                }
            }

            return redirect()->route('maintenance.service-requests.show', $serviceRequest)
                ->with('success', 'Service request has been completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete service request. ' . $e->getMessage());
        }
    }
} 