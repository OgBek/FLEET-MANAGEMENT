@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
        <div class="text-center">
            <h1 class="text-9xl font-bold text-red-600">500</h1>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
                Server Error
            </h2>
            <p class="mt-2 text-base text-gray-500">
                Sorry, something went wrong on our end. We're working to fix it.
            </p>
            <div class="mt-6">
                <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Return Home
                </a>
            </div>
        </div>
        <div class="mt-8 border-t border-gray-200 pt-8">
            <p class="text-sm text-gray-500 text-center">
                If this problem persists, please contact the system administrator with error code: {{ $exception->getMessage() ?? 'Unknown Error' }}
            </p>
        </div>
    </div>
</div>
@endsection 