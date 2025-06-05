@extends('layouts.dashboard')

@push('scripts')
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/vehicle-validation.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image_upload-input');
            const imageBase64Input = document.getElementById('image-base64');
            const preview = document.getElementById('preview-image_upload');

            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const base64Data = e.target.result;
                            if (base64Data.startsWith('data:image/')) {
                                imageBase64Input.value = base64Data;
                            }
                        }
                        reader.readAsDataURL(file);
                    } else {
                        imageBase64Input.value = '';
                    }
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto">
        @include('profile.partials.status-messages')
        
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Add New Vehicle</h3>
                    <a href="{{ route('admin.vehicles.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Back to List
                    </a>
                </div>

                <form action="{{ route('admin.vehicles.store') }}" method="POST" class="space-y-6" data-validate enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehicle Image -->
                        <div class="col-span-2">
                            <x-file-validation 
                                name="image_upload" 
                                accept="image/*" 
                                :maxSize="5" 
                            />
                            <input type="hidden" name="image" id="image-base64" value="{{ old('image') }}">
                        </div>

                        <div>
                            <label for="registration_number" class="block text-sm font-medium text-gray-700">Registration Number</label>
                            <input type="text" 
                                   name="registration_number" 
                                   id="registration_number" 
                                   value="{{ old('registration_number') }}" 
                                   required
                                   data-validate-registration
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase"
                                   pattern="^[A-Z0-9]{2,10}$"
                                   title="Registration number must be 2-10 characters long and contain only uppercase letters and numbers">
                            <x-form-validation name="registration_number" />
                        </div>

                        <div>
                            <label for="vin_number" class="block text-sm font-medium text-gray-700">VIN Number</label>
                            <input type="text" 
                                   name="vin_number" 
                                   id="vin_number" 
                                   value="{{ old('vin_number') }}" 
                                   required
                                   data-validate-vin
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase"
                                   pattern="^[A-HJ-NPR-Z0-9]{17}$"
                                   title="VIN must be exactly 17 characters (letters and numbers, excluding I, O, Q)">
                            <x-form-validation name="vin_number" />
                        </div>

                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                            <input type="text" 
                                   name="model" 
                                   id="model" 
                                   value="{{ old('model') }}" 
                                   required
                                   pattern="^[A-Za-z0-9\s-]{2,50}$"
                                   title="Model must be 2-50 characters long and can contain letters, numbers, spaces and hyphens"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-form-validation name="model" />
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                            <input type="number" 
                                   name="year" 
                                   id="year" 
                                   value="{{ old('year') }}" 
                                   required
                                   data-validate-year
                                   min="1900"
                                   max="{{ date('Y') + 1 }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-form-validation name="year" />
                        </div>

                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (seats)</label>
                            <x-number-validation 
                                name="capacity" 
                                min="1" 
                                max="100" 
                                :value="old('capacity')"
                                required />
                        </div>

                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
                            <input type="text" 
                                   name="color" 
                                   id="color" 
                                   value="{{ old('color') }}" 
                                   required
                                   pattern="^[A-Za-z\s-]{2,30}$"
                                   title="Color must be 2-30 characters long and contain only letters, spaces and hyphens"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-form-validation name="color" />
                        </div>

                        <div>
                            <label for="type_id" class="block text-sm font-medium text-gray-700">Vehicle Type</label>
                            <select name="type_id" 
                                    id="type_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->category->name }})
                                    </option>
                                @endforeach
                            </select>
                            <x-form-validation name="type_id" />
                        </div>

                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700">Brand</label>
                            <select name="brand_id" 
                                    id="brand_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-form-validation name="brand_id" />
                        </div>

                        <div>
                            <label for="engine_number" class="block text-sm font-medium text-gray-700">Engine Number</label>
                            <input type="text" 
                                   name="engine_number" 
                                   id="engine_number" 
                                   value="{{ old('engine_number') }}" 
                                   required
                                   pattern="^[A-Z0-9]{6,20}$"
                                   title="Engine number must be 6-20 characters long and contain only uppercase letters and numbers"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase">
                            <x-form-validation name="engine_number" />
                        </div>

                        <div>
                            <label for="fuel_type" class="block text-sm font-medium text-gray-700">Fuel Type</label>
                            <select name="fuel_type" 
                                    id="fuel_type" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Fuel Type</option>
                                <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                                <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                                <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electric</option>
                                <option value="hybrid" {{ old('fuel_type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                            @error('fuel_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_mileage" class="block text-sm font-medium text-gray-700">Current Mileage</label>
                            <input type="number" name="current_mileage" id="current_mileage" value="{{ old('current_mileage', 0) }}" required min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('current_mileage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="insurance_expiry" class="block text-sm font-medium text-gray-700">Insurance Expiry Date</label>
                            <input type="date" name="insurance_expiry" id="insurance_expiry" value="{{ old('insurance_expiry') }}" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('insurance_expiry')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_maintenance_date" class="block text-sm font-medium text-gray-700">Last Maintenance Date</label>
                            <input type="date" 
                                   name="last_maintenance_date" 
                                   id="last_maintenance_date" 
                                   value="{{ old('last_maintenance_date') }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('last_maintenance_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" 
                                    id="status" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="out_of_service" {{ old('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="initial_mileage" class="block text-sm font-medium text-gray-700">Initial Mileage</label>
                            <input type="number" name="initial_mileage" id="initial_mileage" value="{{ old('initial_mileage', 0) }}" required min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('initial_mileage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="maintenance_interval" class="block text-sm font-medium text-gray-700">Maintenance Interval (km)</label>
                            <input type="number" name="maintenance_interval" id="maintenance_interval" value="{{ old('maintenance_interval', 5000) }}" required min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('maintenance_interval')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label for="features" class="block text-sm font-medium text-gray-700">Features</label>
                            <textarea name="features" id="features" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('features') }}</textarea>
                            @error('features')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition">
                            Create Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection