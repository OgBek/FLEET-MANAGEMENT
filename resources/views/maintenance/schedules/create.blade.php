@extends('layouts.dashboard')

@section('header')
    Create Maintenance Schedule
@endsection

@section('navigation')
    <a href="{{ route('maintenance.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Dashboard
    </a>
    <a href="{{ route('maintenance.tasks.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Maintenance Tasks
    </a>
    <a href="{{ route('maintenance.service-requests.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Service Requests
    </a>
    <a href="{{ route('maintenance.schedules.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
        Schedules
    </a>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Maintenance Schedule</h3>
                
                <form action="{{ route('maintenance.schedules.store') }}" method="POST">
                    @csrf
                    
                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div>
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Vehicle Selection -->
                        <div class="sm:col-span-2">
                            <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                            <div class="mt-1">
                                <select name="vehicle_id" id="vehicle_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->registration_number }} - 
                                            {{ $vehicle->brand->name }} {{ $vehicle->model }}
                                            ({{ $vehicle->year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('vehicle_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Maintenance Type -->
                        <div class="sm:col-span-2">
                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                            <div class="mt-1">
                                <input type="text" name="maintenance_type" id="maintenance_type" value="{{ old('maintenance_type') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Type of maintenance to be performed">
                            </div>
                            @error('maintenance_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Description -->
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                                <span class="text-gray-500 text-xs">(Please provide detailed information)</span>
                            </label>
                            <div class="mt-1">
                                <textarea id="description" name="description" rows="4"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Detailed description of the maintenance tasks to be performed">{{ old('description') }}</textarea>
                            </div>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Scheduled Date -->
                        <div class="sm:col-span-2">
                            <label for="scheduled_date" class="block text-sm font-medium text-gray-700">
                                Scheduled Date
                            </label>
                            <div class="mt-1">
                                <input type="date" name="scheduled_date" id="scheduled_date" 
                                       value="{{ old('scheduled_date') }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            @error('scheduled_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('maintenance.schedules.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create Maintenance Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Once a maintenance schedule is created, the vehicle will be marked as unavailable for booking.</li>
                            <li>Make sure to select an appropriate date that allows enough time for the maintenance to be completed.</li>
                            <li>The maintenance status will be updated as you progress through the tasks.</li>
                            <li>Only vehicles that are not currently under maintenance can be selected.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
