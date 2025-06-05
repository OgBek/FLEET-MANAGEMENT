@extends('layouts.security-guest')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <h2 class="text-2xl font-bold text-center mb-8">Set Up Security Questions</h2>
            <p class="mb-4">Please set up your security questions. These will be used to verify your identity if you need to reset your password.</p>
            <p class="mb-4">Choose <strong>3</strong> security questions and provide answers that you will remember.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4">
                <div class="font-medium text-red-600 dark:text-red-400">{{ __('Whoops! Something went wrong.') }}</div>

                <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('security-questions.store', ['userId' => $user->id]) }}" id="securityQuestionsForm">
            @csrf

            <div class="mt-4">
                <x-label for="question1" value="{{ __('Security Question 1') }}" />
                <select id="question1" name="questions[0]" class="block mt-1 w-full dark:bg-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                    <option value="">Select a security question</option>
                    @foreach($securityQuestions as $question)
                        <option value="{{ $question->id }}" data-question="{{ $question->question }}">{{ $question->question }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-label for="answer1" value="{{ __('Answer 1') }}" />
                <x-input id="answer1" class="block mt-1 w-full" type="text" name="answers[0]" required autocomplete="off" />
            </div>

            <div class="mt-4">
                <x-label for="question2" value="{{ __('Security Question 2') }}" />
                <select id="question2" name="questions[1]" class="block mt-1 w-full dark:bg-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                    <option value="">Select a security question</option>
                    @foreach($securityQuestions as $question)
                        <option value="{{ $question->id }}" data-question="{{ $question->question }}">{{ $question->question }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-label for="answer2" value="{{ __('Answer 2') }}" />
                <x-input id="answer2" class="block mt-1 w-full" type="text" name="answers[1]" required autocomplete="off" />
            </div>

            <div class="mt-4">
                <x-label for="question3" value="{{ __('Security Question 3') }}" />
                <select id="question3" name="questions[2]" class="block mt-1 w-full dark:bg-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                    <option value="">Select a security question</option>
                    @foreach($securityQuestions as $question)
                        <option value="{{ $question->id }}" data-question="{{ $question->question }}">{{ $question->question }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-label for="answer3" value="{{ __('Answer 3') }}" />
                <x-input id="answer3" class="block mt-1 w-full" type="text" name="answers[2]" required autocomplete="off" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Submit') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionSelects = [
        document.getElementById('question1'),
        document.getElementById('question2'),
        document.getElementById('question3')
    ];

    const form = document.getElementById('securityQuestionsForm');
    const selectedQuestions = new Set();

    // Function to update available options
    function updateAvailableOptions() {
        // Clear the set of selected questions
        selectedQuestions.clear();

        // Get all currently selected values
        questionSelects.forEach(select => {
            if (select.value) {
                selectedQuestions.add(select.value);
            }
        });

        // Update each dropdown's available options
        questionSelects.forEach(select => {
            const currentValue = select.value;
            const options = Array.from(select.options);

            options.forEach(option => {
                if (option.value && option.value !== currentValue) {
                    // Disable if the option is selected in another dropdown
                    option.disabled = selectedQuestions.has(option.value);
                    // Hide disabled options
                    option.style.display = option.disabled ? 'none' : '';
                }
            });
        });
    }

    // Add change event listeners to all selects
    questionSelects.forEach(select => {
        select.addEventListener('change', updateAvailableOptions);
    });

    // Initialize on page load
    updateAvailableOptions();

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const selectedValues = new Set();
        let hasError = false;

        questionSelects.forEach(select => {
            if (!select.value) {
                hasError = true;
                return;
            }
            if (selectedValues.has(select.value)) {
                hasError = true;
                return;
            }
            selectedValues.add(select.value);
        });

        if (hasError) {
            e.preventDefault();
            alert('Please select three different security questions');
        }
    });
});
</script>
@endpush
