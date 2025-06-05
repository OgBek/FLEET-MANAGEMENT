@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Edit Maintenance Record</h3>
                    <a href="{{ route('admin.maintenance.index') }}" 
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

                <form action="{{ route('admin.maintenance.update', $maintenance) }}" method="POST" class="space-y-6">
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
                                    <option value="{{ $vehicle->id }}" {{ $maintenance->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->registration_number }} - {{ $vehicle->model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Maintenance Staff -->
                        <div>
                            <label for="maintenance_staff_id" class="block text-sm font-medium text-gray-700">Maintenance Staff</label>
                            <select name="maintenance_staff_id" 
                                    id="maintenance_staff_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($maintenanceStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ $maintenance->maintenance_staff_id == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Type -->
                        <div>
                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select name="maintenance_type" 
                                    id="maintenance_type" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($serviceTypes as $type)
                                    <option value="{{ $type }}" {{ $maintenance->maintenance_type == $type ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" 
                                    id="priority" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="low" {{ $maintenance->priority == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $maintenance->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $maintenance->priority == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $maintenance->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>

                        <!-- Scheduled Date -->
                        <div>
                            <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                            <input type="datetime-local" 
                                   name="scheduled_date" 
                                   id="scheduled_date" 
                                   value="{{ $maintenance->scheduled_date->format('Y-m-d\TH:i') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Estimated Completion Date -->
                        <div>
                            <label for="estimated_completion_date" class="block text-sm font-medium text-gray-700">Estimated Completion Date</label>
                            <input type="datetime-local" 
                                   name="estimated_completion_date" 
                                   id="estimated_completion_date" 
                                   value="{{ $maintenance->estimated_completion_date->format('Y-m-d\TH:i') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $maintenance->description }}</textarea>
                        </div>

                        <!-- Parts Required -->
                        <div class="md:col-span-2">
                            <label for="parts_required" class="block text-sm font-medium text-gray-700">Parts Required</label>
                            <textarea name="parts_required" 
                                      id="parts_required" 
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $maintenance->parts_required }}</textarea>
                        </div>

                        <!-- Estimated Cost -->
                        <div>
                            <label for="estimated_cost" class="block text-sm font-medium text-gray-700">Estimated Cost</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="estimated_cost" 
                                       id="estimated_cost" 
                                       value="{{ $maintenance->estimated_cost }}"
                                       step="0.01"
                                       min="0"
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">birr</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" 
                                    id="status" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $maintenance->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        @if($maintenance->status === 'completed' || $maintenance->status === 'in_progress')
                            <!-- Actual Cost -->
                            <div>
                                <label for="cost" class="block text-sm font-medium text-gray-700">Actual Cost</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="cost" 
                                           id="cost" 
                                           value="{{ $maintenance->cost }}"
                                           step="0.01"
                                           min="0"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">birr</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Labor Hours -->
                            <div>
                                <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours</label>
                                <input type="number" 
                                       name="labor_hours" 
                                       id="labor_hours" 
                                       value="{{ $maintenance->labor_hours }}"
                                       step="0.5"
                                       min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Odometer Reading -->
                            <div>
                                <label for="odometer_reading" class="block text-sm font-medium text-gray-700">Odometer Reading</label>
                                <input type="number" 
                                       name="odometer_reading" 
                                       id="odometer_reading" 
                                       value="{{ $maintenance->odometer_reading }}"
                                       step="1"
                                       min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Next Service Date -->
                            <div>
                                <label for="next_service_date" class="block text-sm font-medium text-gray-700">Next Service Date</label>
                                <input type="date" 
                                       name="next_service_date" 
                                       id="next_service_date" 
                                       value="{{ $maintenance->next_service_date ? $maintenance->next_service_date->format('Y-m-d') : '' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        @endif

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $maintenance->notes }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.maintenance.show', $maintenance) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Maintenance Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 