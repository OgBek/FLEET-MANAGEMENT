@extends('layouts.dashboard')

@section('header')
    Edit User
@endsection

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit User: {{ $user->name }}</h2>
        </div>

        <div class="p-4 sm:p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6" id="userEditForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <x-form-field
                        type="text"
                        name="name"
                        label="Name"
                        :value="old('name', $user->name)"
                        required
                        pattern="^[a-zA-Z\s]{2,}$"
                        minlength="2"
                        maxlength="255"
                        helpText="Enter full name (letters and spaces only)" />

                    <!-- Email -->
                    <x-form-field
                        type="email"
                        name="email"
                        label="Email"
                        :value="old('email', $user->email)"
                        required
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                        helpText="Enter a valid email address" />

                    <!-- Phone -->
                    <x-phone-input 
                        name="phone"
                        label="Phone"
                        :value="old('phone', $user->phone)"
                        required />

                    <!-- Role -->
                    <x-form-field
                        type="select"
                        name="role"
                        label="Role"
                        :value="old('role', $user->roles->first()->name ?? '')"
                        :options="collect($roles)->pluck('name', 'name')->map(function($name) {
                            return ucfirst($name);
                        })"
                        required
                        helpText="Select user role" />

                    <!-- Department -->
                    <x-form-field
                        type="select"
                        name="department_id"
                        label="Department"
                        :value="old('department_id', $user->department_id)"
                        :options="$departments->pluck('name', 'id')"
                        required
                        helpText="Select user department" />

                    <!-- License Number (for drivers) -->
                    <div x-data="{ showLicense: '{{ old('role', $user->roles->first()->name ?? '') }}' === 'driver' }" 
                         x-show="showLicense"
                         class="transition-all duration-300">
                        <x-form-field
                            type="text"
                            name="license_number"
                            label="License Number"
                            :value="old('license_number', $user->license_number)"
                            pattern="^[A-Za-z0-9]{5,15}$"
                            minlength="5"
                            maxlength="15"
                            helpText="Enter valid license number (5-15 characters, letters and numbers only)" />
                    </div>

                    <!-- Specialization (for maintenance staff) -->
                    <div x-data="{ showSpec: '{{ old('role', $user->roles->first()->name ?? '') }}' === 'mechanic' }"
                         x-show="showSpec"
                         class="transition-all duration-300">
                        <x-form-field
                            type="text"
                            name="specialization"
                            label="Specialization"
                            :value="old('specialization', $user->specialization)"
                            pattern="^[a-zA-Z\s\-]{3,50}$"
                            minlength="3"
                            maxlength="50"
                            helpText="Enter specialization (e.g., Engine Repair, Brake Systems)" />
                    </div>

                    <!-- Status -->
                    <x-form-field
                        type="select"
                        name="status"
                        label="Account Status"
                        :value="old('status', $user->status)"
                        :options="[
                            'active' => 'Active',
                            'inactive' => 'Inactive'
                        ]"
                        required
                        helpText="Select account status" />

                    <!-- Approval Status -->
                    <x-form-field
                        type="select"
                        name="approval_status"
                        label="Approval Status"
                        :value="old('approval_status', $user->approval_status)"
                        :options="[
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected'
                        ]"
                        required
                        helpText="Select approval status" />

                    <!-- Password -->
                    <div x-data="{ showPassword: false }">
                        <x-form-field
                            type="password"
                            name="password"
                            label="Password"
                            helpText="Leave blank to keep current password. Min 8 characters with letters, numbers, and symbols"
                            pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                            minlength="8"
                            maxlength="255"
                            title="Password must contain at least 8 characters, including letters, numbers, and symbols" />
                    </div>

                    <!-- Password Confirmation -->
                    <div x-show="showPassword">
                        <x-form-field
                            type="password"
                            name="password_confirmation"
                            label="Confirm Password"
                            helpText="Re-enter the new password" />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('userEditForm');
            const roleSelect = document.getElementById('role');
            const licenseDiv = document.querySelector('[x-data*="showLicense"]');
            const specDiv = document.querySelector('[x-data*="showSpec"]');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');

            // Role-based field validation
            roleSelect.addEventListener('change', function() {
                const role = this.value;
                
                // Show/hide and set required fields based on role
                if (role === 'driver') {
                    document.getElementById('license_number').required = true;
                    licenseDiv.style.display = 'block';
                    specDiv.style.display = 'none';
                    document.getElementById('specialization').required = false;
                } else if (role === 'mechanic') {
                    document.getElementById('specialization').required = true;
                    specDiv.style.display = 'block';
                    licenseDiv.style.display = 'none';
                    document.getElementById('license_number').required = false;
                } else {
                    licenseDiv.style.display = 'none';
                    specDiv.style.display = 'none';
                    document.getElementById('license_number').required = false;
                    document.getElementById('specialization').required = false;
                }
            });

            // Password validation
            password.addEventListener('input', function() {
                if (this.value) {
                    passwordConfirm.required = true;
                    if (this.value !== passwordConfirm.value) {
                        passwordConfirm.setCustomValidity('Passwords do not match');
                    } else {
                        passwordConfirm.setCustomValidity('');
                    }
                } else {
                    passwordConfirm.required = false;
                    passwordConfirm.setCustomValidity('');
                }
            });

            passwordConfirm.addEventListener('input', function() {
                if (password.value !== this.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Initialize role-based fields
            roleSelect.dispatchEvent(new Event('change'));
        });
    </script>
    @endpush
@endsection