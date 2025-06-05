@extends('layouts.dashboard')

@section('header')
    Vehicle Report Details
@endsection

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Report Status Banner -->
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 
                    @if($vehicleReport->status === 'resolved') bg-green-50
                    @elseif($vehicleReport->status === 'in_progress') bg-yellow-50
                    @else bg-gray-50 @endif">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Report #{{ $vehicleReport->id }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Submitted on {{ $vehicleReport->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @if($vehicleReport->status === 'resolved') bg-green-100 text-green-800
                            @elseif($vehicleReport->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $vehicleReport->status)) }}
                        </span>
                    </div>
                </div>

                <!-- Report Details -->
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $vehicleReport->vehicle->name }} - {{ $vehicleReport->vehicle->registration_number }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $vehicleReport->type)) }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->title }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->description }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Severity</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 text-sm inline-flex leading-5 font-semibold rounded-full
                                    @if($vehicleReport->severity === 'high') bg-red-100 text-red-800
                                    @elseif($vehicleReport->severity === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($vehicleReport->severity) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $vehicleReport->location }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Action Buttons -->
                <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between">
                        <a href="{{ route('driver.vehicle-reports.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Reports
                        </a>
                        
                        @if($vehicleReport->status === 'pending')
                            <div class="flex space-x-3">
                                <a href="{{ route('driver.vehicle-reports.edit', $vehicleReport) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Edit Report
                                </a>
                                <form action="{{ route('driver.vehicle-reports.destroy', $vehicleReport) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to delete this report?')"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Delete Report
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
