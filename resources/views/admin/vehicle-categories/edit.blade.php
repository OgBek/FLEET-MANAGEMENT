@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Edit Vehicle Category</h3>
                    <a href="{{ route('admin.vehicle-categories.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Back to List
                    </a>
                </div>

                <form action="{{ route('admin.vehicle-categories.update', $vehicleCategory) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $vehicleCategory->name) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description', $vehicleCategory->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority Level -->
                    <div>
                        <label for="priority_level" class="block text-sm font-medium text-gray-700">Priority Level</label>
                        <select name="priority_level" 
                                id="priority_level" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('priority_level') border-red-300 @enderror">
                            <option value="">Select Priority Level</option>
                            <option value="1" {{ (old('priority_level', $vehicleCategory->priority_level) == 1) ? 'selected' : '' }}>High Priority (VIP)</option>
                            <option value="2" {{ (old('priority_level', $vehicleCategory->priority_level) == 2) ? 'selected' : '' }}>Medium Priority (Department)</option>
                            <option value="3" {{ (old('priority_level', $vehicleCategory->priority_level) == 3) ? 'selected' : '' }}>Low Priority (General)</option>
                        </select>
                        @error('priority_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Vehicle Types -->
                    <div>
                        <label for="vehicle_types" class="block text-sm font-medium text-gray-700">Vehicle Types</label>
                        <select name="vehicle_types[]" 
                                id="vehicle_types" 
                                multiple
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('vehicle_types') border-red-300 @enderror">
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}" 
                                    {{ in_array($type->id, old('vehicle_types', $vehicleCategory->types->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Hold Ctrl (Windows) or Command (Mac) to select multiple types</p>
                        @error('vehicle_types')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 