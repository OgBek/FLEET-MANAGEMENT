<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Vehicle Report Details</h1>
                        <div class="flex space-x-3">
                            <a href="{{ route('maintenance.tasks.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Back to Tasks
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

                    <!-- Status Update Form -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                        <form action="{{ route('maintenance.vehicle-reports.update-status', $vehicleReport) }}" 
                              method="POST" 
                              class="space-y-4"
                              id="status-update-form">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status:</label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="pending" {{ $vehicleReport->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $vehicleReport->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $vehicleReport->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="cancelled" {{ $vehicleReport->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            
                            <!-- Resolution Notes (shown only when status is resolved) -->
                            <div id="resolution-notes-container" class="{{ $vehicleReport->status === 'resolved' ? '' : 'hidden' }}">
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes:</label>
                                <textarea name="resolution_notes" id="resolution_notes" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                          placeholder="Enter resolution details...">{{ $vehicleReport->resolution_notes }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Maintenance Schedule Details -->
                    @if($vehicleReport->maintenanceSchedule)
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Schedule</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $vehicleReport->maintenanceSchedule->type)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($vehicleReport->maintenanceSchedule->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($vehicleReport->maintenanceSchedule->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($vehicleReport->maintenanceSchedule->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($vehicleReport->maintenanceSchedule->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->maintenanceSchedule->scheduled_date->format('F d, Y') }}</dd>
                            </div>
                            @if($vehicleReport->maintenanceSchedule->completed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Completed Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->maintenanceSchedule->completed_at->format('F d, Y') }}</dd>
                            </div>
                            @endif
                            @if($vehicleReport->maintenanceSchedule->notes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->maintenanceSchedule->notes }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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
