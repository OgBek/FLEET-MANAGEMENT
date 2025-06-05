@extends('layouts.dashboard')

@section('header')
    Edit Booking #{{ $booking->id }}
@endsection

@section('content')
    <!-- Toast container for notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>
    
    <!-- Debug section -->
    @if($errors->any())
        <div class="max-w-3xl mx-auto mb-4 bg-red-100 p-4 rounded border border-red-300">
            <h3 class="font-bold text-red-800">Form Validation Errors:</h3>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li class="text-red-700">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-3xl mx-auto">
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <form action="{{ route('client.bookings.update', $booking) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Vehicle Selection -->
                    <div class="mb-6">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select id="vehicle_id" 
                                name="vehicle_id" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required>
                            <option value="">Select a vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $booking->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} - {{ $vehicle->formatted_brand }} {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Driver Selection -->
                    <div class="mb-6">
                        <label for="driver_id" class="block text-sm font-medium text-gray-700">Driver</label>
                        <select id="driver_id" 
                                name="driver_id" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required>
                            <option value="">Select a driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $booking->driver_id) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} - {{ $driver->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Purpose -->
                    <div class="mb-6">
                        <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                        <textarea id="purpose" 
                                 name="purpose" 
                                 rows="3" 
                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                 placeholder="Describe the purpose of this booking"
                                 required>{{ old('purpose', $booking->purpose) }}</textarea>
                        @error('purpose')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Destination -->
                    <div class="mb-6">
                        <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                        <input type="text" 
                               id="destination" 
                               name="destination" 
                               value="{{ old('destination', $booking->destination) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="Enter destination"
                               required>
                        @error('destination')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pickup Location -->
                    <div class="mb-6">
                        <label for="pickup_location" class="block text-sm font-medium text-gray-700">Pickup Location</label>
                        <input type="text" 
                               id="pickup_location" 
                               name="pickup_location" 
                               value="{{ old('pickup_location', $booking->pickup_location) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="Enter pickup location"
                               required>
                        @error('pickup_location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Number of Passengers -->
                    <div class="mb-6">
                        <label for="number_of_passengers" class="block text-sm font-medium text-gray-700">Number of Passengers</label>
                        <input type="number" 
                               id="number_of_passengers" 
                               name="number_of_passengers" 
                               value="{{ old('number_of_passengers', $booking->number_of_passengers) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="Enter number of passengers"
                               min="1"
                               @if($booking->vehicle) max="{{ $booking->vehicle->capacity }}" @endif
                               required>
                        @error('number_of_passengers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="datetime-local" 
                                   name="start_time" 
                                   id="start_time"
                                   step="any"
                                   value="{{ old('start_time', $booking->start_time->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="datetime-local" 
                                   name="end_time" 
                                   id="end_time"
                                   step="any"
                                   value="{{ old('end_time', $booking->end_time->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <a href="{{ route('client.bookings.show', $booking) }}" 
                           class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Update Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Duration validation function
        function validateBookingDuration() {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            if (startTimeInput.value && endTimeInput.value) {
                const startTime = new Date(startTimeInput.value);
                const endTime = new Date(endTimeInput.value);
                
                if (endTime <= startTime) {
                    alert('End time must be after start time');
                    return false;
                }
                
                // Calculate duration in milliseconds
                const durationMs = endTime - startTime;
                const durationMinutes = durationMs / (1000 * 60);
                const durationHours = durationMs / (1000 * 60 * 60);
                const durationDays = durationMs / (1000 * 60 * 60 * 24);
                
                // Check if booking starts and ends on different days
                const startDate = startTime.toDateString();
                const endDate = endTime.toDateString();
                const differentDays = startDate !== endDate;
                
                // Only apply minimum duration for same-day bookings
                if (!differentDays && durationMinutes < 30) {
                    alert('Booking duration must be at least 30 minutes for same-day bookings');
                    return false;
                }
                
                // Get department type from data attribute if it exists
                const isAdmin = document.querySelector('body').dataset.isAdmin === 'true';
                const maxHours = isAdmin ? 120 : 72; // 5 days or 3 days
                
                if (durationHours > maxHours) {
                    alert('Booking duration cannot exceed ' + (isAdmin ? '5 days' : '3 days'));
                    return false;
                }
            }
            
            return true;
        }
        
        // Add form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!validateBookingDuration()) {
                e.preventDefault();
            }
        });
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
        });
    });
    </script>
    @endpush
@endsection 