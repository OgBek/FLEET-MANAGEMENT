@extends('layouts.security-guest')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <h2 class="text-2xl font-bold text-center mb-8">{{ __('Verify Your Identity') }}</h2>
            <p class="mb-4">{{ __('Please answer your security questions to verify your identity.') }}</p>
            <p class="mb-4 font-bold">{{ __('Email:') }} {{ $user->email }}</p>
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

        <form method="POST" action="{{ route('password.security.verify') }}">
            @csrf

            @foreach($securityAnswers as $index => $answer)
                <div class="mt-4">
                    <x-label for="answer{{ $answer->security_question_id }}" value="{{ $answer->securityQuestion->question }}" />
                    <x-input id="answer{{ $answer->security_question_id }}" class="block mt-1 w-full" type="text" name="answers[{{ $answer->security_question_id }}]" required autocomplete="off" />
                </div>
            @endforeach

            <div class="flex items-center justify-between mt-4">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.security.request') }}">
                    {{ __('Back') }}
                </a>
                
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.contact') }}">
                    {{ __('Contact Support') }}
                </a>

                <x-button>
                    {{ __('Verify') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
