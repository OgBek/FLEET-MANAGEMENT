@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg">
            <!-- Header -->
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Vehicle Details</h3>
                    <div class="flex space-x-3">
                        @if($vehicle->status === 'available')
                            <a href="{{ route('client.bookings.create', ['vehicle' => $vehicle->id]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Book Now
                            </a>
                        @endif
                        <a href="{{ route('client.vehicles.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="px-4 py-5 sm:p-6">
                <!-- Vehicle Image -->
                @if($vehicle->image_url)
                    <div class="mb-6">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Vehicle Image</dt>
                        <dd class="mt-1">
                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}" class="h-48 w-48 object-cover rounded-lg shadow-sm">
                        </dd>
                    </div>
                @endif

                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->registration_number }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $vehicle->status === 'available' ? 'bg-green-100 text-green-800' : 
                                   ($vehicle->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800') }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->formatted_brand }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->model }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->category->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Color</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->color }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Year</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->year }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fuel Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($vehicle->fuel_type) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Passenger Capacity</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->capacity }} persons</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Features</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->features ?: 'No features listed' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Vehicle Availability -->
            <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Availability Status</h4>
                <div class="text-sm text-gray-500">
                    @if($vehicle->status === 'available')
                        <p class="text-green-600">This vehicle is currently available for booking.</p>
                    @elseif($vehicle->status === 'maintenance')
                        <p class="text-yellow-600">This vehicle is currently under maintenance and not available for booking.</p>
                    @else
                        <p class="text-red-600">This vehicle is currently not available for booking.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
