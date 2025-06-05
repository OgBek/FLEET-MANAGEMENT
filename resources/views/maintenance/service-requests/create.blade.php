@extends('layouts.dashboard')

@section('header')
    Create Service Request
@endsection

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Service Request</h3>
                
                <form action="{{ route('maintenance.service-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Vehicle Selection -->
                        <div class="sm:col-span-2">
                            <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                            <div class="mt-1">
                                <select name="vehicle_id" id="vehicle_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->registration_number }} - 
                                            {{ $vehicle->formatted_brand }} {{ $vehicle->model }}
                                            ({{ $vehicle->year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('vehicle_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Issue Title -->
                        <div class="sm:col-span-2">
                            <label for="issue_title" class="block text-sm font-medium text-gray-700">Issue Title</label>
                            <div class="mt-1">
                                <input type="text" name="issue_title" id="issue_title" value="{{ old('issue_title') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Brief description of the issue">
                            </div>
                            @error('issue_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Issue Description -->
                        <div class="sm:col-span-2">
                            <label for="issue_description" class="block text-sm font-medium text-gray-700">
                                Issue Description
                                <span class="text-gray-500 text-xs">(Please provide detailed information)</span>
                            </label>
                            <div class="mt-1">
                                <textarea name="issue_description" id="issue_description" rows="4"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Detailed description of the issue, including any symptoms or observations">{{ old('issue_description') }}</textarea>
                            </div>
                            @error('issue_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                            <div class="mt-1">
                                <select name="priority" id="priority"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                                        Low - Regular maintenance or minor issues
                                    </option>
                                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>
                                        Medium - Requires attention but not urgent
                                    </option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                                        High - Significant issue affecting vehicle operation
                                    </option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                                        Urgent - Critical issue requiring immediate attention
                                    </option>
                                </select>
                            </div>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Scheduled Date -->
                        <div>
                            <label for="scheduled_date" class="block text-sm font-medium text-gray-700">
                                Preferred Service Date
                                <span class="text-gray-500 text-xs">(Subject to approval)</span>
                            </label>
                            <div class="mt-1">
                                <input type="datetime-local" name="scheduled_date" id="scheduled_date" 
                                       value="{{ old('scheduled_date') }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            @error('scheduled_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="sm:col-span-2">
                            <label for="additional_notes" class="block text-sm font-medium text-gray-700">
                                Additional Notes
                                <span class="text-gray-500 text-xs">(Optional)</span>
                            </label>
                            <div class="mt-1">
                                <textarea name="additional_notes" id="additional_notes" rows="3"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Any additional information that might be helpful">{{ old('additional_notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('maintenance.service-requests.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Submit Service Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Your service request will be reviewed by an administrator for approval.</li>
                            <li>Once approved, the vehicle will be marked as unavailable for booking during the scheduled maintenance period.</li>
                            <li>You will be notified of the request status via email and dashboard notifications.</li>
                            <li>Priority levels affect response time and scheduling.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 