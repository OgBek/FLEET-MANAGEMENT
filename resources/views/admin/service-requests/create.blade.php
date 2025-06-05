@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Create Service Request</h1>
        <p class="mt-2 text-sm text-gray-700">Create a new maintenance service request for a vehicle.</p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <form action="{{ route('admin.service-requests.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <!-- Vehicle Selection -->
                <div class="sm:col-span-3">
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <select id="vehicle_id" name="vehicle_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Select a vehicle</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->model }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Issue Title -->
                <div class="sm:col-span-3">
                    <label for="issue_title" class="block text-sm font-medium text-gray-700">Issue Title</label>
                    <input type="text" name="issue_title" id="issue_title" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <!-- Priority -->
                <div class="sm:col-span-3">
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select id="priority" name="priority" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Request Type -->
                <div class="sm:col-span-3">
                    <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                    <select id="request_type" name="request_type" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        @foreach ($requestTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Scheduled Date -->
                <div class="sm:col-span-3">
                    <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                    <input type="date" name="scheduled_date" id="scheduled_date" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <!-- Maintenance Staff -->
                <div class="sm:col-span-3">
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select id="assigned_to" name="assigned_to" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Not assigned yet</option>
                        @foreach ($maintenanceStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Issue Description -->
                <div class="sm:col-span-6">
                    <label for="issue_description" class="block text-sm font-medium text-gray-700">Issue Description</label>
                    <textarea id="issue_description" name="issue_description" rows="4" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                    <p class="mt-2 text-sm text-gray-500">Detailed description of the issue or maintenance needed.</p>
                </div>

                <!-- Additional Notes -->
                <div class="sm:col-span-6">
                    <label for="additional_notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                    <textarea id="additional_notes" name="additional_notes" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="{{ route('admin.service-requests.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Service Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 