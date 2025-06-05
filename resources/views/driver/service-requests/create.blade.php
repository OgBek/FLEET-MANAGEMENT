@extends('layouts.dashboard')

@section('header')
    Create Service Request
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('driver.service-requests.store') }}" method="POST">
                @csrf
                
                @if($inspection)
                    <input type="hidden" name="inspection_id" value="{{ $inspection->id }}">
                    <input type="hidden" name="vehicle_id" value="{{ $inspection->vehicle_id }}">
                    
                    <!-- Vehicle Information (Read-only) -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $inspection->vehicle->registration_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $inspection->vehicle->brand->name }} {{ $inspection->vehicle->model }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $inspection->vehicle->type->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($inspection->vehicle->current_mileage) }} km</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Failed Inspection Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Failed Inspection Items</h3>
                        <div class="bg-red-50 rounded-lg p-4">
                            <ul class="list-disc list-inside space-y-2">
                                @foreach($inspection->getFailedItems() as $category => $items)
                                    <li class="text-sm text-red-800">
                                        <span class="font-medium">{{ Str::title($category) }}:</span>
                                        {{ implode(', ', $items) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <!-- Vehicle Selection -->
                    <div class="mb-6">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select id="vehicle_id" name="vehicle_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('vehicle_id') border-red-300 @enderror">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} - {{ $vehicle->brand->name }} {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Request Type -->
                <div class="mb-6">
                    <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                    <select id="request_type" name="request_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('request_type') border-red-300 @enderror">
                        <option value="">Select Type</option>
                        <option value="mechanical" {{ old('request_type') == 'mechanical' ? 'selected' : '' }}>Mechanical Issue</option>
                        <option value="electrical" {{ old('request_type') == 'electrical' ? 'selected' : '' }}>Electrical Issue</option>
                        <option value="body_damage" {{ old('request_type') == 'body_damage' ? 'selected' : '' }}>Body Damage</option>
                        <option value="tire_wheel" {{ old('request_type') == 'tire_wheel' ? 'selected' : '' }}>Tire/Wheel Issue</option>
                        <option value="fluid_leak" {{ old('request_type') == 'fluid_leak' ? 'selected' : '' }}>Fluid Leak</option>
                        <option value="other" {{ old('request_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('request_type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                    <select id="priority" name="priority" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('priority') border-red-300 @enderror">
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - Can be addressed during next service</option>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium - Needs attention soon</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Requires immediate attention</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent - Vehicle cannot be operated safely</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror"
                            placeholder="Please provide a detailed description of the issue...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    @if($inspection)
                        <a href="{{ route('driver.inspections.show', $inspection) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Back to Inspection
                        </a>
                    @else
                        <a href="{{ route('driver.service-requests.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Service Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 