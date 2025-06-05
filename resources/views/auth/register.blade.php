@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    sign in to your existing account
                </a>
            </p>
        </div>
        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                    placeholder="John Doe">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                    placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                        +251
                    </span>
                    <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}"
                        class="pl-16 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('phone') border-red-500 @enderror"
                        placeholder="9xxxxxxxx"
                        pattern="^9[0-9]{8}$"
                        title="Please enter a valid Ethiopian phone number starting with 9 followed by 8 digits">
                </div>
                @error('phone')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role_id" name="role_id" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('role_id') border-red-500 @enderror">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                <select id="department_id" name="department_id" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('department_id') border-red-500 @enderror">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" data-department-name="{{ strtolower($department->name) }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- License Number (for drivers) -->
            <div class="hidden" id="licenseNumberField">
                <label for="license_number" class="block text-sm font-medium text-gray-700">Driver's License Number</label>
                <input id="license_number" name="license_number" type="text" value="{{ old('license_number') }}"
                    class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('license_number') border-red-500 @enderror"
                    placeholder="DL123456">
                @error('license_number')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specialization (for maintenance staff) -->
            <div class="hidden" id="specializationField">
                <label for="specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
                <input id="specialization" name="specialization" type="text" value="{{ old('specialization') }}"
                    class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('specialization') border-red-500 @enderror"
                    placeholder="General Maintenance">
                @error('specialization')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <x-password-input 
                id="password"
                name="password"
                label="Password"
                required="true"
                minlength="8"
                autocomplete="new-password"
            />

            <!-- Confirm Password -->
            <x-password-input 
                id="password_confirmation"
                name="password_confirmation"
                label="Confirm Password"
                required="true"
                minlength="8"
                autocomplete="new-password"
            />

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Register
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Show/hide conditional fields based on role selection
    document.getElementById('role_id').addEventListener('change', function() {
        const licenseNumberField = document.getElementById('licenseNumberField');
        const specializationField = document.getElementById('specializationField');
        const departmentSelect = document.getElementById('department_id');
        const selectedRole = this.options[this.selectedIndex].text.toLowerCase();

        // Show/hide driver's license field
        if (selectedRole === 'driver') {
            licenseNumberField.classList.remove('hidden');
            document.getElementById('license_number').required = true;
        } else {
            licenseNumberField.classList.add('hidden');
            document.getElementById('license_number').required = false;
        }

        // Show/hide specialization field
        if (selectedRole === 'maintenance_staff') {
            specializationField.classList.remove('hidden');
            document.getElementById('specialization').required = true;
        } else {
            specializationField.classList.add('hidden');
            document.getElementById('specialization').required = false;
        }

        // Filter departments based on role
        Array.from(departmentSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            
            const departmentName = option.getAttribute('data-department-name');
            
            if (selectedRole === 'driver') {
                // Show only Driver department for drivers
                option.style.display = departmentName === 'driver' ? '' : 'none';
            } else if (selectedRole === 'maintenance_staff') {
                // Show only Maintenance department for maintenance staff
                option.style.display = departmentName === 'maintenance' ? '' : 'none';
            } else if (selectedRole === 'department_staff') {
                // Hide Driver and Maintenance departments for department staff
                option.style.display = (departmentName !== 'driver' && departmentName !== 'maintenance') ? '' : 'none';
            } else {
                // Show all departments for other roles
                option.style.display = '';
            }
        });

        // Reset department selection if current selection is not valid for the new role
        const visibleOptions = Array.from(departmentSelect.options).filter(option => option.style.display !== 'none');
        if (!visibleOptions.includes(departmentSelect.selectedOptions[0])) {
            departmentSelect.value = '';
        }
    });

    // Trigger the change event on page load if a role is already selected
    if (document.getElementById('role_id').value) {
        document.getElementById('role_id').dispatchEvent(new Event('change'));
    }

    // Phone number validation and formatting
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        // Remove any non-digit characters
        let value = e.target.value.replace(/\D/g, '');
        
        // Ensure the number starts with 9
        if (value.length > 0 && value[0] !== '9') {
            value = '9' + value.substring(1);
        }
        
        // Limit to 9 digits
        value = value.substring(0, 9);
        
        // Update the input value
        e.target.value = value;
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const phone = phoneInput.value;
        
        // Validate phone number format
        if (!/^9[0-9]{8}$/.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid Ethiopian phone number starting with 9 followed by 8 digits');
            phoneInput.focus();
            return false;
        }
    });

    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = this.querySelector('svg');
            if (type === 'password') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            }
        });
    });
</script>
@endpush

@endsection