@extends('layouts.dashboard')
@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Edit Vehicle Report</h1>
                        <a href="{{ route('admin.vehicle-reports.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                            Back to Reports
                        </a>
                    </div>

                    <form action="{{ route('admin.vehicle-reports.update', $vehicleReport) }}" method="POST" class="mt-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Vehicle Selection -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                                <div class="mb-4">
                                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                                    <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ $vehicleReport->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                                {{ optional($vehicle->brand)->name }} {{ $vehicle->model }} ({{ $vehicle->registration_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Driver Selection -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Driver Information</h3>
                                <div class="mb-4">
                                    <label for="user_id" class="block text-sm font-medium text-gray-700">Driver</label>
                                    <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" {{ $vehicleReport->user_id == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->name }} ({{ $driver->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Report Details -->
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Report Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                    <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach(['mechanical', 'electrical', 'body_damage', 'tire', 'other'] as $type)
                                            <option value="{{ $type }}" {{ $vehicleReport->type == $type ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="severity" class="block text-sm font-medium text-gray-700">Severity</label>
                                    <select id="severity" name="severity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach(['low', 'medium', 'high'] as $severity)
                                            <option value="{{ $severity }}" {{ $vehicleReport->severity == $severity ? 'selected' : '' }}>
                                                {{ ucfirst($severity) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('severity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $vehicleReport->title) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" id="location" name="location" value="{{ old('location', $vehicleReport->location) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="description" name="description" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $vehicleReport->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach(['pending', 'in_progress', 'resolved', 'cancelled'] as $status)
                                        <option value="{{ $status }}" {{ $vehicleReport->status == $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('admin.vehicle-reports.show', $vehicleReport) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
   </div>
    @endsection
