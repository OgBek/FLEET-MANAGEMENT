@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Report Vehicle Issue</h2>
            </div>

            <form action="{{ route('driver.vehicle-reports.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Vehicle Selection -->
                <div>
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Select Vehicle</label>
                    <select name="vehicle_id" id="vehicle_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->model }} - {{ $vehicle->brand->name }}- {{ $vehicle->registration_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Issue Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Issue Type</label>
                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select issue type</option>
                        <option value="mechanical">Mechanical</option>
                        <option value="electrical">Electrical</option>
                        <option value="body_damage">Body Damage</option>
                        <option value="tire">Tire Issue</option>
                        <option value="other">Other</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Issue Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Issue Title</label>
                    <input type="text" name="title" id="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Brief description of the issue">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detailed Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description</label>
                    <textarea name="description" id="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Provide detailed information about the issue"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" id="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Where is the vehicle now?">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Severity -->
                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700">Issue Severity</label>
                    <select name="severity" id="severity" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select severity</option>
                        <option value="low">Low - Vehicle can still operate safely</option>
                        <option value="medium">Medium - Needs attention soon</option>
                        <option value="high">High - Immediate attention required</option>
                    </select>
                    @error('severity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('driver.vehicle-reports.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection