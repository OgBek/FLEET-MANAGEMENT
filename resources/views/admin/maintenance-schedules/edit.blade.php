@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Edit Maintenance Schedule</h3>
                <a href="{{ route('admin.maintenance-schedules.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to List
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-sm text-red-600 rounded-md p-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.maintenance-schedules.update', $maintenanceSchedule) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Vehicle -->
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select name="vehicle_id" 
                                id="vehicle_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $maintenanceSchedule->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} - {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Maintenance Staff -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">Maintenance Staff</label>
                        <select name="assigned_to" 
                                id="assigned_to" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Not Assigned</option>
                            @foreach($maintenanceStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $maintenanceSchedule->assigned_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Maintenance Type -->
                    <div>
                        <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                        <select name="maintenance_type" 
                                id="maintenance_type" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type }}" {{ $maintenanceSchedule->maintenance_type === $type ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" 
                                id="status" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" {{ $maintenanceSchedule->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $maintenanceSchedule->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $maintenanceSchedule->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $maintenanceSchedule->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="overdue" {{ $maintenanceSchedule->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <!-- Scheduled Date -->
                    <div>
                        <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                        <input type="date" 
                               name="scheduled_date" 
                               id="scheduled_date" 
                               value="{{ $maintenanceSchedule->scheduled_date->format('Y-m-d') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Mileage Interval (Optional) -->
                    <div>
                        <label for="mileage_interval" class="block text-sm font-medium text-gray-700">
                            Mileage Interval (Optional)
                        </label>
                        <input type="number" 
                               name="mileage_interval" 
                               id="mileage_interval" 
                               value="{{ $maintenanceSchedule->mileage_interval }}"
                               min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Leave blank if not applicable
                        </p>
                    </div>

                    <!-- Time Interval Days (Optional) -->
                    <div>
                        <label for="time_interval_days" class="block text-sm font-medium text-gray-700">
                            Time Interval in Days (Optional)
                        </label>
                        <input type="number" 
                               name="time_interval_days" 
                               id="time_interval_days" 
                               value="{{ $maintenanceSchedule->time_interval_days }}"
                               min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            For recurring maintenance, how many days between occurrences
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" 
                              id="description" 
                              rows="3" 
                              required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $maintenanceSchedule->description }}</textarea>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $maintenanceSchedule->notes }}</textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.maintenance-schedules.show', $maintenanceSchedule) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Maintenance Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 