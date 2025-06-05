@extends('layouts.dashboard')

@section('header')
    Schedule New Maintenance
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form action="{{ route('maintenance.schedule.store') }}" method="POST">
                    @csrf
                    
                    <!-- Vehicle Selection -->
                    <div class="mb-6">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select id="vehicle_id" name="vehicle_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('vehicle_id') border-red-300 @enderror">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} - {{ $vehicle->model }}
                                    ({{ $vehicle->brand->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Maintenance Type -->
                    <div class="mb-6">
                        <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                        <select id="maintenance_type" name="maintenance_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('maintenance_type') border-red-300 @enderror">
                            <option value="">Select Type</option>
                            <option value="Routine Service" {{ old('maintenance_type') === 'Routine Service' ? 'selected' : '' }}>Routine Service</option>
                            <option value="Oil Change" {{ old('maintenance_type') === 'Oil Change' ? 'selected' : '' }}>Oil Change</option>
                            <option value="Tire Rotation" {{ old('maintenance_type') === 'Tire Rotation' ? 'selected' : '' }}>Tire Rotation</option>
                            <option value="Brake Service" {{ old('maintenance_type') === 'Brake Service' ? 'selected' : '' }}>Brake Service</option>
                            <option value="Major Service" {{ old('maintenance_type') === 'Major Service' ? 'selected' : '' }}>Major Service</option>
                            <option value="Inspection" {{ old('maintenance_type') === 'Inspection' ? 'selected' : '' }}>Inspection</option>
                            <option value="Other" {{ old('maintenance_type') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('maintenance_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Scheduled Date -->
                    <div class="mb-6">
                        <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                        <input type="date" id="scheduled_date" name="scheduled_date" required
                               value="{{ old('scheduled_date') }}"
                               min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('scheduled_date') border-red-300 @enderror">
                        @error('scheduled_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recurring Maintenance -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recurring Maintenance (Optional)</h3>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Mileage Interval -->
                            <div>
                                <label for="mileage_interval" class="block text-sm font-medium text-gray-700">Mileage Interval (km)</label>
                                <input type="number" id="mileage_interval" name="mileage_interval"
                                       value="{{ old('mileage_interval') }}" min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('mileage_interval') border-red-300 @enderror">
                                @error('mileage_interval')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Time Interval -->
                            <div>
                                <label for="time_interval_days" class="block text-sm font-medium text-gray-700">Time Interval (days)</label>
                                <input type="number" id="time_interval_days" name="time_interval_days"
                                       value="{{ old('time_interval_days') }}" min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('time_interval_days') border-red-300 @enderror">
                                @error('time_interval_days')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Set either or both intervals to automatically schedule the next maintenance after completion.
                        </p>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('maintenance.schedule.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Schedule Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 