@extends('layouts.dashboard')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Vehicle Report Details</h1>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.vehicle-reports.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Back to Reports
                            </a>
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehicle Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                @if($vehicleReport->vehicle)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Make & Model</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->vehicle->formatted_brand }} {{ $vehicleReport->vehicle->model }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->vehicle->registration_number }}</dd>
                                    </div>
                                    @if($vehicleReport->vehicle->type && $vehicleReport->vehicle->type->category)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->vehicle->type->category->name }}</dd>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500">Vehicle information not available</div>
                                @endif
                            </dl>
                        </div>

                        <!-- Driver Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Driver Information</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                @if($vehicleReport->driver)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->driver->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->driver->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->driver->phone ?? 'Not provided' }}</dd>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">Driver information not available</div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Report Details -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Report Details</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date Submitted</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->created_at ? $vehicleReport->created_at->format('F d, Y H:i:s') : 'Date not available' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if(!$vehicleReport->status) bg-gray-100 text-gray-800
                                        @elseif($vehicleReport->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($vehicleReport->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($vehicleReport->status === 'resolved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $vehicleReport->status ? ucfirst(str_replace('_', ' ', $vehicleReport->status)) : 'Unknown' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->title ?? 'No title available' }}</dd>
                            </div>
                            @if($vehicleReport->type)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($vehicleReport->type === 'mechanical') bg-blue-100 text-blue-800
                                            @elseif($vehicleReport->type === 'electrical') bg-yellow-100 text-yellow-800
                                            @elseif($vehicleReport->type === 'body_damage') bg-red-100 text-red-800
                                            @elseif($vehicleReport->type === 'tire') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $vehicleReport->type)) }}
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if($vehicleReport->severity)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Severity</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($vehicleReport->severity === 'high') bg-red-100 text-red-800
                                            @elseif($vehicleReport->severity === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst($vehicleReport->severity) }}
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if($vehicleReport->location)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->location }}</dd>
                                </div>
                            @endif
                            @if($vehicleReport->description)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $vehicleReport->description }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Maintenance History -->
                    @if($vehicleReport->vehicle)
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance History</h3>
                        
                        <!-- Maintenance Tasks -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Recent Maintenance Tasks</h4>
                            @if($vehicleReport->vehicle->maintenanceTasks->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($vehicleReport->vehicle->maintenanceTasks as $task)
                                        <div class="bg-white p-3 rounded-md shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                                    <p class="text-sm text-gray-500">{{ $task->description }}</p>
                                                </div>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($task->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($task->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                <span>Scheduled: {{ $task->scheduled_date->format('M d, Y') }}</span>
                                                @if($task->completed_at)
                                                    <span class="ml-3">Completed: {{ $task->completed_at->format('M d, Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No recent maintenance tasks found.</p>
                            @endif
                        </div>

                        <!-- Service Requests -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Recent Service Requests</h4>
                            @if($vehicleReport->vehicle->serviceRequests->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($vehicleReport->vehicle->serviceRequests as $request)
                                        <div class="bg-white p-3 rounded-md shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $request->issue_title }}</p>
                                                    <p class="text-sm text-gray-500">{{ Str::limit($request->issue_description, 100) }}</p>
                                                </div>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($request->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800
                                                    @elseif($request->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                <span>Reported: {{ $request->created_at->format('M d, Y') }}</span>
                                                @if($request->completed_at)
                                                    <span class="ml-3">Resolved: {{ $request->completed_at->format('M d, Y') }}</span>
                                                @endif
                                                <span class="ml-3">Priority: {{ ucfirst($request->priority) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No recent service requests found.</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    <!-- Actions -->
                    <div class="mt-8 flex justify-between items-center">
                        <!-- Status Update Form -->
                        <div class="relative">
                            @if($vehicleReport->status === 'resolved')
                                <!-- Only show resolution notes when status is resolved -->
                                <div class="mb-4">
                                    <h4 class="text-md font-medium text-gray-700 mb-2">Resolution Notes:</h4>
                                    <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                        @php
                                            // Find maintenance schedule that was created around the same time as this report was resolved
                                            $maintenanceSchedule = null;
                                            if ($vehicleReport->vehicle_id && $vehicleReport->updated_at) {
                                                // Look for maintenance schedules created within 24 hours of the report being updated
                                                $maintenanceSchedule = \App\Models\MaintenanceSchedule::where('vehicle_id', $vehicleReport->vehicle_id)
                                                    ->where('created_at', '>=', $vehicleReport->updated_at->subDay())
                                                    ->where('created_at', '<=', $vehicleReport->updated_at->addDay())
                                                    ->first();
                                            }
                                            $resolutionNotes = $maintenanceSchedule ? $maintenanceSchedule->resolution_notes : null;
                                        @endphp
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $resolutionNotes ?: 'No resolution notes provided.' }}</p>
                                    </div>
                                </div>
                            @else
                                <form action="{{ route('admin.vehicle-reports.update-status', $vehicleReport) }}" 
                                      method="POST" 
                                      class="inline-flex items-center space-x-4"
                                      id="status-update-form">
                                    @csrf
                                    @method('PATCH')
                                    <label for="status" class="text-sm font-medium text-gray-700">Update Status:</label>
                                    <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <option value="pending" {{ $vehicleReport->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $vehicleReport->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $vehicleReport->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="cancelled" {{ $vehicleReport->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    
                                    <!-- Resolution Notes (shown only when status is resolved) -->
                                    <div id="resolution-notes-container" class="{{ $vehicleReport->status === 'resolved' ? '' : 'hidden' }}">
                                        <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes:</label>
                                        <textarea name="resolution_notes" id="resolution_notes" rows="3" 
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                  placeholder="Enter resolution details...">{{ $vehicleReport->resolution_notes }}</textarea>
                                    </div>

                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                        Update Status
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="flex space-x-3">
                            @if($vehicleReport->status !== 'resolved')
                                <a href="{{ route('admin.vehicle-reports.edit', $vehicleReport) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                    Edit Report
                                </a>
                                <form action="{{ route('admin.vehicle-reports.destroy', $vehicleReport) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                        Delete Report
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   

<script>
    // Show/hide resolution notes based on status
    document.getElementById('status').addEventListener('change', function() {
        const resolutionNotesContainer = document.getElementById('resolution-notes-container');
        if (this.value === 'resolved') {
            resolutionNotesContainer.classList.remove('hidden');
        } else {
            resolutionNotesContainer.classList.add('hidden');
        }
    });
</script>
@endsection