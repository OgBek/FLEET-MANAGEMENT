@extends('layouts.dashboard')

@section('header')
    Create New Booking
@endsection

@section('content')
<div class="container mx-auto px-6 py-8">
    <h3 class="text-gray-700 text-3xl font-medium">Create New Booking</h3>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-red-800 font-medium">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-8">
        <form method="POST" action="{{ route('client.bookings.store') }}" class="space-y-6">
            @csrf

            <!-- Vehicle Selection -->
            @if($selectedVehicle)
                <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h4 class="font-medium text-gray-700 mb-2">Selected Vehicle</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600"><span class="font-medium">Registration:</span> {{ $selectedVehicle->registration_number }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Model:</span> {{ $selectedVehicle->model }} ({{ $selectedVehicle->year }})</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Type:</span> {{ optional($selectedVehicle->type)->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600"><span class="font-medium">Brand:</span> {{ optional($selectedVehicle->brand)->name }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Category:</span> {{ optional(optional($selectedVehicle->type)->category)->name }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Capacity:</span> {{ $selectedVehicle->capacity }} persons</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Vehicle <span class="text-red-500">*</span>
                        </label>
                        <select name="vehicle_id" id="vehicle_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                            <option value="">Select a vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" 
                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}
                                    data-capacity="{{ $vehicle->capacity }}">
                                    {{ $vehicle->registration_number }} - {{ $vehicle->model }} ({{ $vehicle->year }}) - Capacity: {{ $vehicle->capacity }} persons
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <!-- Driver Selection -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4">
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Driver <span class="text-red-500">*</span>
                    </label>
                    <select name="driver_id" id="driver_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                        <option value="">Select a driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }} - {{ $driver->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 space-y-4">
                    <!-- Purpose -->
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                            Purpose <span class="text-red-500">*</span>
                            <span class="text-gray-500 text-xs">(max 500 characters)</span>
                        </label>
                        <textarea name="purpose" id="purpose" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required maxlength="500">{{ old('purpose') }}</textarea>
                    </div>

                    <!-- Destination -->
                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">
                            Destination <span class="text-red-500">*</span>
                            <span class="text-gray-500 text-xs">(max 255 characters)</span>
                        </label>
                        <input type="text" name="destination" id="destination" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            value="{{ old('destination') }}" required maxlength="255">
                    </div>

                    <!-- Pickup Location -->
                    <div>
                        <label for="pickup_location" class="block text-sm font-medium text-gray-700 mb-2">
                            Pickup Location <span class="text-red-500">*</span>
                            <span class="text-gray-500 text-xs">(max 255 characters)</span>
                        </label>
                        <input type="text" name="pickup_location" id="pickup_location" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            value="{{ old('pickup_location') }}" required maxlength="255">
                    </div>

                    <!-- Number of Passengers -->
                    <div>
                        <label for="number_of_passengers" class="block text-sm font-medium text-gray-700 mb-2">
                            Number of Passengers <span class="text-red-500">*</span>
                            @if($selectedVehicle)
                                <span class="text-gray-500 text-xs">(max {{ $selectedVehicle->capacity }} persons)</span>
                            @endif
                        </label>
                        <input type="number" name="number_of_passengers" id="number_of_passengers" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            value="{{ old('number_of_passengers', 1) }}" min="1" 
                            @if($selectedVehicle) max="{{ $selectedVehicle->capacity }}" @endif required>
                    </div>

                    <!-- Time Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="start_time" id="start_time" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                value="{{ old('start_time') }}" required
                                min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}">
                            <p class="mt-1 text-sm text-gray-500">Must be at least 30 minutes from now</p>
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                End Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="end_time" id="end_time" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                value="{{ old('end_time') }}" required>
                            <p class="mt-1 text-sm text-gray-500">Maximum duration: 72 hours</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                            <span class="text-gray-500 text-xs">(optional, max 500 characters)</span>
                        </label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            maxlength="500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Booking
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const vehicleSelect = document.getElementById('vehicle_id');
    const passengersInput = document.getElementById('number_of_passengers');
    const bookingForm = document.querySelector('form');
    
    // Function to show modern alert
    async function showAlert(title, message, icon = 'error') {
        return await Swal.fire({
            title: title,
            html: message,
            icon: icon,
            confirmButtonText: 'Got it',
            confirmButtonColor: '#3b82f6',
            customClass: {
                confirmButton: 'px-4 py-2 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
            },
            buttonsStyling: false
        });
    }

    // Function to validate time range
    async function validateTimeRange() {
        if (!startTimeInput.value || !endTimeInput.value) return true;
        
        const startTime = new Date(startTimeInput.value);
        const endTime = new Date(endTimeInput.value);
        
        if (endTime <= startTime) {
            await showAlert(
                'Invalid Time Range', 
                'The end time must be after the start time. Please adjust your booking times.'
            );
            endTimeInput.value = '';
            endTimeInput.focus();
            return false;
        }
        return true;
    }
    
    // Function to set minimum end time based on start time
    function updateEndTimeMin() {
        if (startTimeInput.value) {
            // Set minimum end time to 30 minutes after start time
            const minEndTime = new Date(startTimeInput.value);
            minEndTime.setMinutes(minEndTime.getMinutes() + 30);
            endTimeInput.min = minEndTime.toISOString().slice(0, 16);
            
            // If current end time is before the new minimum, clear it
            if (endTimeInput.value && new Date(endTimeInput.value) < minEndTime) {
                endTimeInput.value = '';
            }
        }
    }
    
    // Function to check vehicle availability when time changes
    async function checkVehicleAvailability() {
        if (!startTimeInput.value || !endTimeInput.value || !vehicleSelect.value) return;
        
        // First validate the time range
        if (!(await validateTimeRange())) return;
        
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const vehicleId = vehicleSelect.value;
        
        try {
            const response = await fetch(`/fleet/client/bookings/check-availability/${vehicleId}?start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`);
            const data = await response.json();
            
            if (!data.available) {
                await showAlert(
                    'Vehicle Not Available',
                    'This vehicle is not available for the selected time period. Please choose another vehicle or select a different time slot.',
                    'warning'
                );
                vehicleSelect.value = '';
            }
        } catch (error) {
            console.error('Error checking availability:', error);
            await showAlert(
                'Error',
                'An error occurred while checking vehicle availability. Please try again.',
                'error'
            );
        }
    }
    
    // Update max passengers based on vehicle capacity
    function updateMaxPassengers() {
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.capacity) {
            const capacity = parseInt(selectedOption.dataset.capacity);
            passengersInput.setAttribute('max', capacity);
            
            // If current value exceeds max, adjust it
            if (parseInt(passengersInput.value) > capacity) {
                passengersInput.value = capacity;
            }
        }
    }
    
    // Event listeners
    startTimeInput.addEventListener('change', function() {
        updateEndTimeMin();
        checkVehicleAvailability();
    });
    
    endTimeInput.addEventListener('change', function() {
        validateTimeRange();
        checkVehicleAvailability();
    });
    
    vehicleSelect.addEventListener('change', function() {
        updateMaxPassengers();
        checkVehicleAvailability();
    });
    
    // Form submission validation
    bookingForm.addEventListener('submit', function(e) {
        if (!validateTimeRange()) {
            e.preventDefault();
            return false;
        }
        return true;
    });
    
    // Initial setup
    updateMaxPassengers();
    updateEndTimeMin();
});
</script>
@endpush
@endsection