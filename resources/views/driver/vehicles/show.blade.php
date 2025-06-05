@extends('layouts.dashboard')

@section('header')
    Vehicle Details - {{ $vehicle->registration_number }}
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Status Banner -->
    <div class="mb-6">
        <div class="rounded-md p-4 
            @if($vehicle->status === 'available') bg-green-50
            @elseif($vehicle->status === 'maintenance') bg-yellow-50
            @elseif($vehicle->status === 'out_of_service') bg-red-50
            @else bg-blue-50
            @endif">
            <div class="flex">
                <div class="flex-shrink-0">
                    @if($vehicle->status === 'available')
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($vehicle->status === 'maintenance')
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    @elseif($vehicle->status === 'out_of_service')
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium 
                        @if($vehicle->status === 'available') text-green-800
                        @elseif($vehicle->status === 'maintenance') text-yellow-800
                        @elseif($vehicle->status === 'out_of_service') text-red-800
                        @else text-blue-800
                        @endif">
                        Vehicle Status: {{ Str::title(str_replace('_', ' ', $vehicle->status)) }}
                    </h3>
                    <div class="mt-2 text-sm 
                        @if($vehicle->status === 'available') text-green-700
                        @elseif($vehicle->status === 'maintenance') text-yellow-700
                        @elseif($vehicle->status === 'out_of_service') text-red-700
                        @else text-blue-700
                        @endif">
                        @if($vehicle->status === 'maintenance')
                            Vehicle is currently under maintenance
                            @if($vehicle->maintenanceSchedules()->where('status', 'in_progress')->first())
                                - Expected completion: {{ $vehicle->maintenanceSchedules()->where('status', 'in_progress')->first()->scheduled_date->format('M d, Y') }}
                            @endif
                        @elseif($vehicle->status === 'out_of_service')
                            Vehicle is temporarily out of service
                        @elseif($vehicle->status === 'booked')
                            Vehicle is currently booked for a trip
                        @else
                            Vehicle is available for assignments
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <!-- Basic Information -->
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Vehicle Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->registration_number }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Brand & Model</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->formatted_brand }} {{ $vehicle->model }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->category->name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Year</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->year }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Color</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->color }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Seating Capacity</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->seating_capacity }} passengers</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Fuel Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Str::title($vehicle->fuel_type) }}</dd>
                </div>
            </dl>
        </div>

        <!-- Technical Details -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Technical Details</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">VIN Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->vin_number }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Engine Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->engine_number }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($vehicle->current_mileage) }} km</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Insurance Expiry</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $vehicle->insurance_expiry->format('M d, Y') }}
                        @if($vehicle->insurance_expiry->isPast())
                            <span class="text-red-600 font-medium">- Expired</span>
                        @elseif($vehicle->insurance_expiry->diffInDays(now()) < 30)
                            <span class="text-yellow-600 font-medium">- Expiring Soon</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Maintenance Information -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Maintenance Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Maintenance</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($vehicle->last_maintenance_date)
                            {{ $vehicle->last_maintenance_date->format('M d, Y') }}
                            ({{ $vehicle->last_maintenance_date->diffForHumans() }})
                        @else
                            No maintenance record found
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Next Scheduled Maintenance</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($nextMaintenance = $vehicle->maintenanceSchedules()->where('status', 'pending')->orderBy('scheduled_date')->first())
                            {{ $nextMaintenance->scheduled_date->format('M d, Y') }}
                            @if($nextMaintenance->scheduled_date->isPast())
                                <span class="text-red-600 font-medium">- Overdue</span>
                            @elseif($nextMaintenance->scheduled_date->diffInDays(now()) < 7)
                                <span class="text-yellow-600 font-medium">- Due Soon</span>
                            @endif
                        @else
                            No maintenance scheduled
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Features -->
        @if($vehicle->features)
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Vehicle Features</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    @foreach($vehicle->features as $feature)
                        <li class="flex items-center text-sm text-gray-900">
                            <svg class="h-5 w-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 flex justify-end space-x-3">
            <a href="{{ route('driver.inspections.create', ['vehicle' => $vehicle->id]) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Perform Inspection
            </a>
            <a href="{{ route('driver.service-requests.create', ['vehicle' => $vehicle->id]) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Create Service Request
            </a>
        </div>
    </div>
</div>
@endsection 