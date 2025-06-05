@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Booking</h2>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department_id" name="department_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (old('department_id') ?? $booking->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vehicle Information (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <div class="mt-1">
                        @if($booking->vehicle)
                            <p class="text-sm text-gray-900">
                                {{ $booking->vehicle->brand->name }} {{ $booking->vehicle->model }}
                                ({{ $booking->vehicle->registration_number }})
                                <input type="hidden" name="vehicle_id" value="{{ $booking->vehicle_id }}">
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $booking->vehicle->type->category->name }} - {{ $booking->vehicle->type->name }}
                            </p>
                        @else
                            <div class="rounded-md bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Vehicle No Longer Available</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>This booking was made for a vehicle that has been removed from the system. The original vehicle information is preserved for record-keeping.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Driver -->
                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700">Driver</label>
                    <select id="driver_id" name="driver_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ (old('driver_id') ?? $booking->driver_id) == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                        @if($booking->status === 'completed')
                            <div class="mt-1 p-2 bg-gray-100 rounded-md">
                                {{ $booking->start_time->format('M d, Y H:i') }}
                                <input type="hidden" name="start_time" value="{{ $booking->start_time->format('Y-m-d\TH:i') }}">
                            </div>
                        @else
                            <input type="datetime-local" 
                                   name="start_time" 
                                   id="start_time"
                                   step="any"
                                   value="{{ old('start_time', $booking->start_time->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @endif
                        @error('start_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                        @if($booking->status === 'completed')
                            <div class="mt-1 p-2 bg-gray-100 rounded-md">
                                {{ $booking->end_time->format('M d, Y H:i') }}
                                <input type="hidden" name="end_time" value="{{ $booking->end_time->format('Y-m-d\TH:i') }}">
                            </div>
                        @else
                            <input type="datetime-local" 
                                   name="end_time" 
                                   id="end_time"
                                   step="any"
                                   value="{{ old('end_time', $booking->end_time->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @endif
                        @error('end_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Purpose -->
                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                    <textarea id="purpose" name="purpose" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('purpose', $booking->purpose) }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pickup Location -->
                <div>
                    <label for="pickup_location" class="block text-sm font-medium text-gray-700">Pickup Location</label>
                    <input type="text" id="pickup_location" name="pickup_location" 
                           value="{{ old('pickup_location', $booking->pickup_location) }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('pickup_location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Destination -->
                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                    <input type="text" id="destination" name="destination" 
                           value="{{ old('destination', $booking->destination) }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('destination')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number of Passengers -->
                <div>
                    <label for="number_of_passengers" class="block text-sm font-medium text-gray-700">Number of Passengers</label>
                    <input type="number" id="number_of_passengers" name="number_of_passengers" 
                           value="{{ old('number_of_passengers', $booking->number_of_passengers) }}"
                           required min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('number_of_passengers')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" 
                            name="status" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" {{ (old('status') ?? $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ (old('status') ?? $booking->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ (old('status') ?? $booking->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ (old('status') ?? $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="in_progress" {{ (old('status') ?? $booking->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ (old('status') ?? $booking->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $booking->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.bookings.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Format date to YYYY-MM-DDThh:mm
            function formatDateForInput(date) {
                return date.getFullYear().toString() +
                    '-' + (date.getMonth() + 1).toString().padStart(2, '0') +
                    '-' + date.getDate().toString().padStart(2, '0') +
                    'T' + date.getHours().toString().padStart(2, '0') +
                    ':' + date.getMinutes().toString().padStart(2, '0');
            }

            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            // Set minimum date to current device time
            const now = new Date();
            const minDateTime = formatDateForInput(now);
            startTimeInput.min = minDateTime;
            
            // Update end time minimum when start time changes
            startTimeInput.addEventListener('change', function() {
                const startDate = new Date(this.value);
                const minEndDate = new Date(startDate.getTime() + (30 * 60 * 1000)); // Minimum 30 minutes duration
                endTimeInput.min = formatDateForInput(minEndDate);
                
                // If end time is before new minimum, update it
                const endDate = new Date(endTimeInput.value);
                if (endDate < minEndDate) {
                    endTimeInput.value = formatDateForInput(minEndDate);
                }
            });

            // Form validation before submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const startDate = new Date(startTimeInput.value);
                const endDate = new Date(endTimeInput.value);
                const now = new Date();

                if (startDate < now) {
                    e.preventDefault();
                    alert('Start time cannot be in the past. Please select a future time.');
                    return;
                }

                if (endDate <= startDate) {
                    e.preventDefault();
                    alert('End time must be after start time.');
                    return;
                }

                const duration = (endDate - startDate) / (60 * 1000); // Duration in minutes
                if (duration < 30) {
                    e.preventDefault();
                    alert('Booking duration must be at least 30 minutes.');
                    return;
                }

                // Add timezone to form data
                const timezoneInput = document.createElement('input');
                timezoneInput.type = 'hidden';
                timezoneInput.name = 'timezone';
                timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
                form.appendChild(timezoneInput);
            });

            // Add dynamic vehicle capacity check
            const vehicleSelect = document.getElementById('vehicle_id');
            const passengersInput = document.getElementById('number_of_passengers');
            const vehicles = @json($vehicles);

            vehicleSelect.addEventListener('change', function() {
                const selectedVehicle = vehicles.find(v => v.id == this.value);
                if (selectedVehicle && selectedVehicle.capacity) {
                    passengersInput.max = selectedVehicle.capacity;
                    if (parseInt(passengersInput.value) > selectedVehicle.capacity) {
                        passengersInput.value = selectedVehicle.capacity;
                    }
                }
            });

            // Trigger initial vehicle capacity check
            if (vehicleSelect.value) {
                vehicleSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
@endsection