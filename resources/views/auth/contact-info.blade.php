@extends('layouts.security-guest')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-center mb-8 text-gray-900 dark:text-gray-100">{{ __('Contact Support') }}</h2>
            
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                {{ __('If you\'re having trouble accessing your account, our support team is here to help. You can reach us through any of the following methods:') }}
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-gray-100">{{ __('Phone Support') }}</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ __('+251 972718887') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Available Monday-Friday, 9:00 AM - 5:00 PM') }}</p>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-gray-100">{{ __('Email Support') }}</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ __('support@dilla.edu.et') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('We aim to respond within 24 hours') }}</p>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-gray-100">{{ __('Visit Our Office') }}</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ __('ODAYAA CAMPUS, DILLA, ETHIOPIA') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Office hours: Monday-Friday, 9:00 AM - 5:00 PM') }}</p>
            </div>
            
            <p class="mt-6 text-gray-600 dark:text-gray-400">
                {{ __('When contacting support, please be ready to provide your full name, email address, and any other information that will help us verify your identity.') }}
            </p>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Back to login') }}
            </a>
            
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.security.request') }}">
                {{ __('Try security questions') }}
            </a>
        </div>
    </div>
</div>
@endsection
