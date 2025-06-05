@extends('layouts.dashboard')

@section('header')
    Edit Service Request #{{ $request->id }}
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form action="{{ route('maintenance.service-requests.update', $request) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Vehicle Information (Read-only) -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $request->vehicle->registration_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vehicle Details</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $request->vehicle->formatted_brand }} {{ $request->vehicle->model }} ({{ $request->vehicle->year }})
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($request->vehicle->current_mileage) }} km</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vehicle Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($request->vehicle->status === 'available') bg-green-100 text-green-800
                                            @elseif($request->vehicle->status === 'maintenance') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($request->vehicle->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Request Type -->
                    <div class="mb-6">
                        <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                        <select id="request_type" name="request_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('request_type') border-red-300 @enderror">
                            <option value="Repair" {{ $request->request_type === 'Repair' ? 'selected' : '' }}>Repair</option>
                            <option value="Maintenance" {{ $request->request_type === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="Inspection" {{ $request->request_type === 'Inspection' ? 'selected' : '' }}>Inspection</option>
                            <option value="Breakdown" {{ $request->request_type === 'Breakdown' ? 'selected' : '' }}>Breakdown</option>
                            <option value="Other" {{ $request->request_type === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('request_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="mb-6">
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                        <select id="priority" name="priority" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('priority') border-red-300 @enderror">
                            <option value="low" {{ $request->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $request->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $request->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $request->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>Priority Guidelines:</strong><br>
                            - Urgent: Vehicle is inoperable or unsafe (immediate attention required)<br>
                            - High: Serious issue affecting vehicle performance<br>
                            - Medium: Non-critical issues that should be addressed soon<br>
                            - Low: Minor issues or routine maintenance
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror"
                                  placeholder="Please provide detailed information about the issue or service needed...">{{ old('description', $request->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Location (if breakdown) -->
                    <div class="mb-6 {{ $request->request_type !== 'Breakdown' ? 'hidden' : '' }}" id="locationSection">
                        <label for="current_location" class="block text-sm font-medium text-gray-700">Current Location</label>
                        <input type="text" id="current_location" name="current_location"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('current_location', $request->current_location) }}"
                               placeholder="Enter the vehicle's current location if broken down">
                        @error('current_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror"
                                  placeholder="Any additional information that might be helpful...">{{ old('notes', $request->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Update (if authorized) -->
                    @if(auth()->user()->hasRole('maintenance_staff') || auth()->user()->hasRole('admin'))
                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-300 @enderror">
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('maintenance.service-requests.show', $request) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show/hide location field based on request type
        document.getElementById('request_type').addEventListener('change', function() {
            const locationSection = document.getElementById('locationSection');
            if (this.value === 'Breakdown') {
                locationSection.classList.remove('hidden');
            } else {
                locationSection.classList.add('hidden');
            }
        });
    </script>
    @endpush
@endsection 