@extends('layouts.dashboard')

@section('header')
    Edit Maintenance Task: {{ $task->title }}
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Edit Maintenance Task
                        </h3>
                    </div>
                </div>

                <form method="POST" action="{{ route('maintenance.tasks.update', $task) }}" class="divide-y divide-gray-200">
                    @csrf
                    @method('PUT')
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="divide-y divide-gray-200">
                        <!-- Title -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Title <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <input type="text" 
                                    id="title" 
                                    name="title" 
                                    value="{{ old('title', $task->title) }}" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Description -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Description <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <textarea id="description" 
                                    name="description" 
                                    rows="3" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>{{ old('description', $task->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Maintenance Type -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Maintenance Type <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <select id="maintenance_type" 
                                    name="maintenance_type" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                    <option value="">Select Type</option>
                                    @foreach($maintenanceTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('maintenance_type', $task->maintenance_type) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('maintenance_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Priority -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Priority <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <select id="priority" 
                                    name="priority" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Scheduled Date -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Scheduled Date <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <input type="datetime-local" 
                                    id="scheduled_date" 
                                    name="scheduled_date" 
                                    value="{{ old('scheduled_date', $task->scheduled_date ? $task->scheduled_date->format('Y-m-d\TH:i') : '') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                @error('scheduled_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Estimated Hours -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Estimated Hours <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" 
                                        id="estimated_hours" 
                                        name="estimated_hours" 
                                        step="0.1" 
                                        min="0.5" 
                                        value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                        class="block w-full rounded-md border-gray-300 pr-12 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">hours</span>
                                    </div>
                                </div>
                                @error('estimated_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                        <!-- Vehicle -->
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Vehicle <span class="text-red-500">*</span>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <select id="vehicle_id" 
                                    name="vehicle_id" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $task->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->registration_number }} - {{ $vehicle->brand->name }} {{ $vehicle->model }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </dd>
                        </div>

                            </dl>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-4 bg-gray-50 text-right sm:px-6">
                        <a href="{{ route('maintenance.tasks.show', $task) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
