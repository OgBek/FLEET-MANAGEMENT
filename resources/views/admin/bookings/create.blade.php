@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Create New Booking</h2>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.bookings.store') }}" method="POST" class="p-6 space-y-6" id="bookingForm">
                @csrf

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department_id" name="department_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vehicle -->
                <div>
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <select id="vehicle_id" name="vehicle_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" 
                                    data-capacity="{{ $vehicle->capacity }}"
                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->formatted_brand }} {{ $vehicle->model }} - {{ $vehicle->type->category->name }} 
                                ({{ $vehicle->type->name }}) - Capacity: {{ $vehicle->capacity }} passengers
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500" id="vehicleCapacityInfo"></p>
                    @error('vehicle_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Driver -->
                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700">Driver</label>
                    <select id="driver_id" name="driver_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number of Passengers -->
                <div>
                    <label for="number_of_passengers" class="block text-sm font-medium text-gray-700">
                        Number of Passengers
                        <span id="maxCapacity" class="text-sm text-gray-500"></span>
                    </label>
                    <input type="number" 
                           id="number_of_passengers" 
                           name="number_of_passengers" 
                           value="{{ old('number_of_passengers') }}"
                           min="1"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('number_of_passengers')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                        <input type="datetime-local" 
                               name="start_time" 
                               id="start_time"
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               step="any"
                               value="{{ old('start_time') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Cannot book for weekends without special approval</p>
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
                               value="{{ old('end_time') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Maximum booking duration is 72 hours</p>
                        @error('end_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Purpose -->
                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                    <textarea id="purpose" 
                              name="purpose" 
                              rows="3"
                              required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pickup Location -->
                <div>
                    <label for="pickup_location" class="block text-sm font-medium text-gray-700">Pickup Location</label>
                    <input type="text" 
                           id="pickup_location" 
                           name="pickup_location" 
                           value="{{ old('pickup_location') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('pickup_location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Destination -->
                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                    <input type="text" 
                           id="destination" 
                           name="destination" 
                           value="{{ old('destination') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('destination')
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
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')
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
                        Create Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize flatpickr for datetime inputs
        flatpickr("#start_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                // Update end time minimum date when start time changes
                endTimePicker.set('minDate', dateStr);
                
                // Clear end time if it's before start time
                if (endTimePicker.selectedDates[0] < selectedDates[0]) {
                    endTimePicker.clear();
                }
            }
        });

        const endTimePicker = flatpickr("#end_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
        });

        // Validate form before submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const startTime = new Date(document.getElementById('start_time').value);
            const endTime = new Date(document.getElementById('end_time').value);
            
            // Calculate duration in hours
            const duration = (endTime - startTime) / (1000 * 60 * 60);
            
            if (duration > 72) {
                e.preventDefault();
                alert('Booking duration cannot exceed 72 hours.');
                return false;
            }
            
            return true;
        });
    </script>
    @endpush
@endsection 