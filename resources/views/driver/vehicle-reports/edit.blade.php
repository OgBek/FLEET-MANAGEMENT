@extends('layouts.dashboard')

@section('header')
    Edit Vehicle Report
@endsection

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Edit Report #{{ $vehicleReport->id }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Update the report details below
                    </p>
                </div>

                <form action="{{ route('driver.vehicle-reports.update', $vehicleReport) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="px-4 py-5 sm:p-6 space-y-6">
                        <!-- Vehicle Selection -->
                        <div>
                            <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                            <select name="vehicle_id" id="vehicle_id" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('vehicle_id') border-red-300 text-red-900 placeholder-red-300 @enderror">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $vehicleReport->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->name }} - {{ $vehicle->registration_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Report Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Report Type</label>
                            <select name="type" id="type" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('type') border-red-300 text-red-900 placeholder-red-300 @enderror">
                                <option value="">Select Type</option>
                                @foreach(['mechanical', 'electrical', 'body_damage', 'tire', 'other'] as $type)
                                    <option value="{{ $type }}" {{ old('type', $vehicleReport->type) == $type ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" 
                                   value="{{ old('title', $vehicleReport->title) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('title') border-red-300 text-red-900 placeholder-red-300 @enderror">
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 text-red-900 placeholder-red-300 @enderror">{{ old('description', $vehicleReport->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Severity -->
                        <div>
                            <label for="severity" class="block text-sm font-medium text-gray-700">Severity</label>
                            <select name="severity" id="severity" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('severity') border-red-300 text-red-900 placeholder-red-300 @enderror">
                                <option value="">Select Severity</option>
                                @foreach(['low', 'medium', 'high'] as $severity)
                                    <option value="{{ $severity }}" {{ old('severity', $vehicleReport->severity) == $severity ? 'selected' : '' }}>
                                        {{ ucfirst($severity) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('severity')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" id="location" 
                                   value="{{ old('location', $vehicleReport->location) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('location') border-red-300 text-red-900 placeholder-red-300 @enderror">
                            @error('location')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <a href="{{ route('driver.vehicle-reports.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
