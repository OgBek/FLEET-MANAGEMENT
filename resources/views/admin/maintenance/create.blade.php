@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Create New Maintenance Record</h3>
                    <a href="{{ route('admin.maintenance.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Back to List
                    </a>
                </div>

                <form action="{{ route('admin.maintenance.store') }}" method="POST" class="space-y-6" id="maintenanceForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehicle Selection -->
                        <x-form-field
                            type="select"
                            name="vehicle_id"
                            label="Vehicle"
                            :value="old('vehicle_id')"
                            :options="$vehicles->pluck('registration_number', 'id')->map(function($reg, $id) use ($vehicles) {
                                $vehicle = $vehicles->find($id);
                                return $reg . ' - ' . $vehicle->brand->name . ' ' . $vehicle->model;
                            })"
                            required
                            helpText="Select the vehicle that needs maintenance" />

                        <!-- Maintenance Staff -->
                        <x-form-field
                            type="select"
                            name="maintenance_staff_id"
                            label="Assigned Mechanic"
                            :value="old('maintenance_staff_id')"
                            :options="$maintenanceStaff->pluck('name', 'id')->map(function($name, $id) use ($maintenanceStaff) {
                                $staff = $maintenanceStaff->find($id);
                                return $name . ' - ' . $staff->specialization;
                            })"
                            required
                            helpText="Select the mechanic to perform the maintenance" />

                        <!-- Maintenance Type -->
                        <x-form-field
                            type="select"
                            name="maintenance_type"
                            label="Maintenance Type"
                            :value="old('maintenance_type')"
                            :options="collect($serviceTypes)->mapWithKeys(function($type) {
                                return [$type => ucfirst(str_replace('_', ' ', $type))];
                            })"
                            required
                            helpText="Select the type of maintenance service" />

                        <!-- Priority Level -->
                        <x-form-field
                            type="select"
                            name="priority"
                            label="Priority Level"
                            :value="old('priority')"
                            :options="[
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent'
                            ]"
                            required
                            helpText="Select the urgency level of this maintenance" />

                        <!-- Scheduled Date -->
                        <x-form-field
                            type="datetime-local"
                            name="scheduled_date"
                            label="Scheduled Date"
                            :value="old('scheduled_date')"
                            required
                            :min="now()->format('Y-m-d\TH:i')"
                            helpText="Must be a future date during working hours (8 AM - 5 PM)" />

                        <!-- Estimated Completion Date -->
                        <x-form-field
                            type="datetime-local"
                            name="estimated_completion_date"
                            label="Estimated Completion Date"
                            :value="old('estimated_completion_date')"
                            required
                            helpText="Must be after the scheduled date" />
                    </div>

                    <!-- Description -->
                    <x-form-field
                        type="textarea"
                        name="description"
                        label="Description"
                        :value="old('description')"
                        required
                        helpText="Provide a detailed description of the maintenance work needed" />

                    <!-- Parts Required -->
                    <x-form-field
                        type="textarea"
                        name="parts_required"
                        label="Parts Required"
                        :value="old('parts_required')"
                        placeholder="List any parts that will be needed for this maintenance"
                        helpText="List all parts needed for the maintenance, one per line" />

                    <!-- Estimated Cost -->
                    <x-form-field
                        type="number"
                        name="estimated_cost"
                        label="Estimated Cost"
                        :value="old('estimated_cost')"
                        min="0"
                        step="0.01"
                        required
                        helpText="Enter the estimated cost in ETB" />

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.maintenance.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            Create Maintenance Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('maintenanceForm');
            const scheduledDateInput = document.getElementById('scheduled_date');
            const estimatedCompletionInput = document.getElementById('estimated_completion_date');

            function validateWorkingHours(date) {
                const hours = date.getHours();
                return hours >= 8 && hours < 17; // 8 AM to 5 PM
            }

            scheduledDateInput.addEventListener('change', function() {
                const scheduledDate = new Date(this.value);
                
                // Validate working hours
                if (!validateWorkingHours(scheduledDate)) {
                    this.setCustomValidity('Maintenance must be scheduled between 8 AM and 5 PM');
                } else {
                    this.setCustomValidity('');
                }

                // Update minimum for estimated completion date
                estimatedCompletionInput.min = this.value;
            });

            estimatedCompletionInput.addEventListener('change', function() {
                const scheduledDate = new Date(scheduledDateInput.value);
                const estimatedDate = new Date(this.value);

                if (estimatedDate <= scheduledDate) {
                    this.setCustomValidity('Estimated completion date must be after the scheduled date');
                } else if (!validateWorkingHours(estimatedDate)) {
                    this.setCustomValidity('Estimated completion time must be between 8 AM and 5 PM');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Set initial minimum dates
            const now = new Date();
            const nowString = now.toISOString().slice(0, 16);
            scheduledDateInput.min = nowString;
        });
    </script>
    @endpush
@endsection