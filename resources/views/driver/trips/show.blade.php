@extends('layouts.dashboard')

@section('header')
    Trip Details
@endsection

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Error Alert -->
        <div id="errorAlert" class="hidden mb-4 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800" id="errorMessage"></h3>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" onclick="hideError()" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div id="successAlert" class="hidden mb-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800" id="successMessage"></h3>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" onclick="hideSuccess()" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Trip Status Banner -->
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 
                    @if($trip->status === 'approved') bg-blue-50
                    @elseif($trip->status === 'in_progress') bg-yellow-50
                    @elseif($trip->status === 'completed') bg-green-50
                    @else bg-gray-50 @endif">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Trip #{{ $trip->id }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Created on {{ $trip->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @if($trip->status === 'approved') bg-blue-100 text-blue-800
                            @elseif($trip->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($trip->status === 'completed') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($trip->status) }}
                        </span>
                    </div>
                </div>

                <!-- Trip Details -->
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                            <dd class="mt-1 text-sm">
                                @if($trip->vehicle)
                                    <span class="text-gray-900">
                                        {{ $trip->vehicle->registration_number }} - 
                                        {{ $trip->vehicle->brand?->name ?? 'N/A' }} {{ $trip->vehicle->model ?? '' }}
                                    </span>
                                @else
                                    <span class="text-red-500">Vehicle not found</span>
                                @endif
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Requester</dt>
                            <dd class="mt-1 text-sm">
                                @if($trip->requestedBy)
                                    <span class="text-gray-900">{{ $trip->requestedBy->name }}</span>
                                    @if($trip->requestedBy->department)
                                        <span class="text-gray-500 block">
                                            {{ $trip->requestedBy->department->name }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-500 italic">No requester information</span>
                                @endif
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trip->purpose }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Destination</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trip->destination }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $trip->start_time->format('M d, Y H:i') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">End Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $trip->end_time->format('M d, Y H:i') }}
                            </dd>
                        </div>

                        @if($trip->actual_start_time)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Actual Start Time</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $trip->actual_start_time->format('M d, Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        @if($trip->actual_end_time)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Actual End Time</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $trip->actual_end_time->format('M d, Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        @if($trip->number_of_passengers)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Number of Passengers</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $trip->number_of_passengers }}</dd>
                            </div>
                        @endif

                        @if($trip->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $trip->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Action Buttons -->
                <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between">
                        <a href="{{ route('driver.trips.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Trips
                        </a>
                        
                        <div class="flex space-x-3">
                            @if($trip->status === 'approved')
                                <button onclick="startTrip()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Start Trip
                                </button>
                            @elseif($trip->status === 'in_progress')
                                <button onclick="completeTrip()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Dropped Off
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').classList.remove('hidden');
        }

        function hideError() {
            document.getElementById('errorAlert').classList.add('hidden');
        }

        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successAlert').classList.remove('hidden');
        }

        function hideSuccess() {
            document.getElementById('successAlert').classList.add('hidden');
        }

        function startTrip() {
            fetch('{{ route('driver.trips.start', $trip) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError(data.error);
                }
            })
            .catch(error => {
                showError('An error occurred while starting the trip.');
                console.error('Error:', error);
            });
        }

        function completeTrip() {
            fetch('{{ route('driver.trips.complete', $trip) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError(data.error);
                }
            })
            .catch(error => {
                showError('An error occurred while completing the trip.');
                console.error('Error:', error);
            });
        }
    </script>
    @endpush
@endsection