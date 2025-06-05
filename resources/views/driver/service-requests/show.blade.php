@extends('layouts.dashboard')

@section('header')
    Service Request #{{ $serviceRequest->id }}
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Status Banner -->
    <div class="mb-6">
        <div class="rounded-md p-4 
            @if($serviceRequest->status === 'completed') bg-green-50 
            @elseif($serviceRequest->status === 'in_progress') bg-yellow-50
            @elseif($serviceRequest->status === 'rejected') bg-red-50 
            @else bg-blue-50 
            @endif">
            <div class="flex">
                <div class="flex-shrink-0">
                    @if($serviceRequest->status === 'completed')
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($serviceRequest->status === 'in_progress')
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($serviceRequest->status === 'rejected')
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium 
                        @if($serviceRequest->status === 'completed') text-green-800
                        @elseif($serviceRequest->status === 'in_progress') text-yellow-800
                        @elseif($serviceRequest->status === 'rejected') text-red-800
                        @else text-blue-800
                        @endif">
                        Status: {{ Str::title($serviceRequest->status) }}
                    </h3>
                    <div class="mt-2 text-sm 
                        @if($serviceRequest->status === 'completed') text-green-700
                        @elseif($serviceRequest->status === 'in_progress') text-yellow-700
                        @elseif($serviceRequest->status === 'rejected') text-red-700
                        @else text-blue-700
                        @endif">
                        <p>
                            @if($serviceRequest->status === 'completed')
                                Service request completed on {{ $serviceRequest->completion_date->format('M d, Y H:i') }}
                            @elseif($serviceRequest->status === 'in_progress')
                                Currently being worked on by {{ $serviceRequest->assignedStaff->name }}
                            @elseif($serviceRequest->status === 'rejected')
                                Service request was rejected
                            @else
                                Awaiting maintenance staff assignment
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <!-- Vehicle Information -->
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Vehicle Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->registration_number }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->brand->name }} {{ $serviceRequest->vehicle->model }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->type->name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($serviceRequest->vehicle->current_mileage) }} km</dd>
                </div>
            </dl>
        </div>

        <!-- Service Request Details -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Service Request Details</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Request Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Str::title(str_replace('_', ' ', $serviceRequest->request_type)) }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Priority</dt>
                    <dd class="mt-1 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($serviceRequest->priority === 'urgent') bg-red-100 text-red-800
                            @elseif($serviceRequest->priority === 'high') bg-orange-100 text-orange-800
                            @elseif($serviceRequest->priority === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ Str::title($serviceRequest->priority) }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Requested Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->requested_date->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceRequest->assignedStaff ? $serviceRequest->assignedStaff->name : 'Not yet assigned' }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->description }}</dd>
                </div>
                @if($serviceRequest->resolution_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Resolution Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->resolution_notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Related Inspection -->
        @if($serviceRequest->inspection)
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Related Inspection</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Inspection #{{ $serviceRequest->inspection->id }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $serviceRequest->inspection->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <a href="{{ route('driver.inspections.show', $serviceRequest->inspection) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        View Inspection Details
                    </a>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 flex justify-end space-x-3">
            <a href="{{ route('driver.service-requests.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Back to Service Requests
            </a>
        </div>
    </div>
</div>
@endsection 