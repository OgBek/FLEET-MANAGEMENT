@extends('layouts.security-guest')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <h2 class="text-2xl font-bold text-center mb-8">{{ __('Reset Password') }}</h2>
            <p class="mb-4">{{ __('Your identity has been verified. Please enter your new password below.') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4">
                <div class="font-medium text-red-600 dark:text-red-400">{{ __('Whoops! Something went wrong.') }}</div>

                <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.security.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mt-4">
                <x-label for="password" value="{{ __('New Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Password must be at least 8 characters and include uppercase, lowercase, number, and special character.</p>
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
