@extends('layouts.dashboard')

@section('header')
    Inspection Details #{{ $inspection->id }}
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Status Banner -->
    <div class="mb-6">
        <div class="rounded-md p-4 
            @if($inspection->hasFailedItems()) bg-red-50 
            @else bg-green-50 
            @endif">
            <div class="flex">
                <div class="flex-shrink-0">
                    @if($inspection->hasFailedItems())
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium 
                        @if($inspection->hasFailedItems()) text-red-800
                        @else text-green-800
                        @endif">
                        Inspection Status: {{ $inspection->hasFailedItems() ? 'Issues Found' : 'All Items Passed' }}
                    </h3>
                    <div class="mt-2 text-sm 
                        @if($inspection->hasFailedItems()) text-red-700
                        @else text-green-700
                        @endif">
                        <p>
                            {{ $inspection->type }} inspection completed on 
                            {{ $inspection->created_at->format('M d, Y H:i') }}
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
                    <dd class="mt-1 text-sm text-gray-900">{{ $inspection->vehicle->registration_number }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $inspection->vehicle->brand->name }} {{ $inspection->vehicle->model }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Odometer Reading</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($inspection->odometer_reading) }} km</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Trip</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('driver.trips.show', $inspection->trip) }}" class="text-blue-600 hover:text-blue-900">
                            View Trip Details
                        </a>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Inspection Results -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Inspection Results</h3>
        </div>

        <!-- Exterior Inspection -->
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Exterior Inspection</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($inspection->exterior_results as $item => $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ Str::title(str_replace('_', ' ', $item)) }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($status === 'pass') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Interior Inspection -->
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Interior Inspection</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($inspection->interior_results as $item => $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ Str::title(str_replace('_', ' ', $item)) }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($status === 'pass') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Safety Equipment -->
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Safety Equipment</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($inspection->safety_results as $item => $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ Str::title(str_replace('_', ' ', $item)) }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($status === 'pass') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Fluid Levels -->
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Fluid Levels</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($inspection->fluid_results as $item => $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ Str::title(str_replace('_', ' ', $item)) }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($status === 'pass') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        @if($inspection->notes)
            <!-- Additional Notes -->
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h4 class="text-base font-medium text-gray-900 mb-4">Additional Notes</h4>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {{ $inspection->notes }}
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="border-t border-gray-200 px-4 py-4 sm:px-6 bg-gray-50">
            <div class="flex justify-end space-x-3">
                <a href="{{ route('driver.trips.show', $inspection->trip) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Back to Trip
                </a>
                @if($inspection->hasFailedItems())
                    <a href="{{ route('driver.service-requests.create', ['inspection' => $inspection->id]) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Create Service Request
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 