<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-password-input 
                    id="password"
                    name="password"
                    label="Password"
                    required="true"
                    minlength="8"
                    autocomplete="new-password"
                />
            </div>

            <div class="mt-4">
                <x-password-input 
                    id="password_confirmation"
                    name="password_confirmation"
                    label="Confirm Password"
                    required="true"
                    minlength="8"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
