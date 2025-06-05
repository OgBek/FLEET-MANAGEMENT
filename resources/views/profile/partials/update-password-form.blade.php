@php
    $profileRoute = 'client.profile.update';
    if (auth()->user()->hasRole('admin')) {
        $profileRoute = 'admin.profile.update';
    } elseif (auth()->user()->hasRole('driver')) {
        $profileRoute = 'driver.profile.update';
    } elseif (auth()->user()->hasRole('maintenance_staff')) {
        $profileRoute = 'maintenance.profile.update';
    }
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Update Password') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route($profileRoute) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-6">
            <div class="col-span-6 sm:col-span-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" name="current_password" id="current_password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required autocomplete="current-password">
                @error('current_password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-6 sm:col-span-4">
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" id="password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required autocomplete="new-password">
                <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters with letters, numbers, and symbols</p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-6 sm:col-span-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required autocomplete="new-password">
                @error('password_confirmation')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Save Password') }}
            </button>
        </div>
    </form>
</section>
