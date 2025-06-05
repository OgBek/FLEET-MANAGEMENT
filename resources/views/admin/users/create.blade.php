@extends('layouts.dashboard')

@push('scripts')
    <script src="{{ asset('js/form-validation.js') }}"></script>
@endpush

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Add New User</h2>
        </div>

        <div class="p-4 sm:p-6">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6" data-validate>
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               required
                               data-validate="name"
                               pattern="^[a-zA-Z\s]{2,50}$"
                               title="Name must be 2-50 characters long and contain only letters"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <x-form-validation name="name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" 
                               required
                               data-validate="email"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <x-form-validation name="email" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <x-phone-input 
                            name="phone"
                            label="Phone"
                            :value="old('phone')"
                            required />
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" 
                                id="role" 
                                required
                                data-validate="role"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <x-form-validation name="role" />
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department_id" 
                                id="department_id" 
                                required
                                data-validate="department"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-form-validation name="department_id" />
                    </div>

                    <!-- License Number (for drivers) -->
                    <div id="license-container" class="hidden">
                        <label for="license_number" class="block text-sm font-medium text-gray-700">License Number</label>
                        <input type="text" 
                               name="license_number" 
                               id="license_number" 
                               value="{{ old('license_number') }}"
                               data-validate="license_number"
                               pattern="^[A-Z0-9-]{5,15}$"
                               title="Please enter a valid license number"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <x-form-validation name="license_number" />
                    </div>

                    <!-- Specialization (for maintenance staff) -->
                    <div id="specialization-container" class="hidden">
                        <label for="specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
                        <input type="text" 
                               name="specialization" 
                               id="specialization" 
                               value="{{ old('specialization') }}"
                               data-validate="specialization"
                               pattern="^[a-zA-Z\s]{2,50}$"
                               title="Specialization must be 2-50 characters long"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <x-form-validation name="specialization" />
                    </div>

                    <!-- Password -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-password-input 
                            id="password"
                            name="password"
                            label="Password"
                            required="true"
                            minlength="8"
                            autocomplete="new-password"
                        />
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-password-input 
                            id="password_confirmation"
                            name="password_confirmation"
                            label="Confirm Password"
                            required="true"
                            minlength="8"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.users.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const departmentSelect = document.getElementById('department_id');
            const licenseContainer = document.getElementById('license-container');
            const specializationContainer = document.getElementById('specialization-container');
            const licenseInput = document.getElementById('license_number');
            const specializationInput = document.getElementById('specialization');
            
            // Find Driver Department option
            let driverDepartmentId = null;
            let driverDepartmentOption = null;
            
            Array.from(departmentSelect.options).forEach(option => {
                if (option.text.toLowerCase().includes('driver')) {
                    driverDepartmentId = option.value;
                    driverDepartmentOption = option;
                }
            });
            
            // Create a label to show when driver is selected
            const driverDepartmentInfo = document.createElement('div');
            driverDepartmentInfo.classList.add('text-sm', 'text-gray-700', 'mt-1', 'hidden');
            driverDepartmentInfo.innerHTML = 'Drivers are automatically assigned to the Driver Department';
            departmentSelect.parentNode.appendChild(driverDepartmentInfo);

            function toggleFields() {
                const selectedRole = roleSelect.value;
                
                if (selectedRole === 'driver') {
                    // Show license field
                    licenseContainer.classList.remove('hidden');
                    licenseInput.required = true;
                    
                    // Hide specialization field
                    specializationContainer.classList.add('hidden');
                    specializationInput.required = false;
                    
                    // Auto-select Driver Department
                    if (driverDepartmentId) {
                        // Show only driver department option in the select
                        Array.from(departmentSelect.options).forEach(option => {
                            if (option.value !== driverDepartmentId && option.value !== '') {
                                option.style.display = 'none';
                            }
                        });
                        
                        // Select driver department
                        departmentSelect.value = driverDepartmentId;
                        
                        // Show info message
                        driverDepartmentInfo.classList.remove('hidden');
                    }
                } else {
                    // Handle non-driver roles
                    
                    // Reset department select to show all options
                    Array.from(departmentSelect.options).forEach(option => {
                        option.style.display = '';
                    });
                    
                    // Hide info message
                    driverDepartmentInfo.classList.add('hidden');
                    
                    if (selectedRole === 'maintenance_staff') {
                        // Show specialization for maintenance staff
                        specializationContainer.classList.remove('hidden');
                        specializationInput.required = true;
                        licenseContainer.classList.add('hidden');
                        licenseInput.required = false;
                    } else {
                        // Hide both for other roles
                        licenseContainer.classList.add('hidden');
                        specializationContainer.classList.add('hidden');
                        licenseInput.required = false;
                        specializationInput.required = false;
                    }
                }
            }

            // Apply logic on role change and initial load
            roleSelect.addEventListener('change', toggleFields);
            toggleFields();
        });
    </script>
@endsection