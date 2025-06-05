@props(['user', 'route'])

<div class="space-y-6">
    <!-- Profile Information Form -->
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Profile Information') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __("Update your account's profile information and email address.") }}
            </p>
        </header>

        <form method="post" action="{{ $route }}" class="mt-6 space-y-6" enctype="multipart/form-data" id="profileForm">
            @csrf
            @method('patch')

            @include('profile.partials.photo-upload')

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

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" 
                       x-show="show" 
                       x-transition 
                       x-init="setTimeout(() => show = false, 2000)" 
                       class="text-sm text-gray-600">
                        {{ __('Saved.') }}
                    </p>
                @endif
            </div>
        </form>
    </section>

    <!-- Password Update Form -->
    <section class="mt-10 pt-10 border-t border-gray-200">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Update Password') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Ensure your account is using a strong password to stay secure.') }}
            </p>
        </header>

        <form method="post" action="{{ $route }}" class="mt-6 space-y-6" id="passwordForm">
            @csrf
            @method('patch')

            <x-form-field
                type="password"
                name="current_password"
                label="Current Password"
                required
                helpText="Enter your current password" />

            <x-form-field
                type="password"
                name="password"
                label="New Password"
                pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                minlength="8"
                maxlength="255"
                required
                helpText="Password must be at least 8 characters with letters, numbers, and symbols" />

            <x-form-field
                type="password"
                name="password_confirmation"
                label="Confirm Password"
                required
                helpText="Re-enter your new password" />

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }"
                       x-show="show"
                       x-transition
                       x-init="setTimeout(() => show = false, 2000)"
                       class="text-sm text-gray-600">
                        {{ __('Saved.') }}
                    </p>
                @endif
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const password = document.querySelector('input[name="password"]');
    const passwordConfirm = document.querySelector('input[name="password_confirmation"]');

    // Profile form validation
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]');
            const email = document.querySelector('input[name="email"]');
            const phone = document.querySelector('input[name="phone"]');

            if (!name.value.match(/^[a-zA-Z\s]{2,}$/)) {
                e.preventDefault();
                name.setCustomValidity('Name must contain only letters and spaces');
                name.reportValidity();
                return;
            }

            if (!email.value.match(/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/)) {
                e.preventDefault();
                email.setCustomValidity('Please enter a valid email address');
                email.reportValidity();
                return;
            }

            if (!phone.value.match(/^9[0-9]{8}$/)) {
                e.preventDefault();
                phone.setCustomValidity('Phone must start with 9 and be exactly 9 digits');
                phone.reportValidity();
                return;
            }
        });
    }

    // Password form validation
    if (passwordForm) {
        password.addEventListener('input', validatePassword);
        passwordConfirm.addEventListener('input', validatePassword);
    }

    function validatePassword() {
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
        
        if (!password.value.match(passwordRegex)) {
            password.setCustomValidity('Password must contain at least 8 characters, including letters, numbers, and symbols');
        } else {
            password.setCustomValidity('');
        }

        if (password.value !== passwordConfirm.value) {
            passwordConfirm.setCustomValidity('Passwords do not match');
        } else {
            passwordConfirm.setCustomValidity('');
        }
    }
});
</script>
@endpush
