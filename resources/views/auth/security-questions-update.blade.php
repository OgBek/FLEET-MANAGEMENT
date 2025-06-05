@extends($layout ?? 'layouts.dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-700 text-3xl font-medium">Update Security Questions</h3>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 space-y-6">
            <p class="text-sm text-gray-600 mb-6">
                {{ __('Security questions help verify your identity if you need to reset your password. Choose 3 security questions and provide answers that you will remember.') }}
            </p>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <div class="font-medium">{{ __('Whoops! Something went wrong.') }}</div>

                    <ul class="mt-3 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ 
                auth()->user()->hasRole('admin') ? route('admin.profile.security-questions.update.post') : 
                (auth()->user()->hasRole('driver') ? route('driver.profile.security-questions.update.post') : 
                (auth()->user()->hasRole('maintenance_staff') ? route('maintenance.profile.security-questions.update.post') : 
                route('client.profile.security-questions.update.post'))) 
            }}" id="securityQuestionsForm">
                @csrf

                <div class="mt-4">
                    <label for="question1" class="block font-medium text-sm text-gray-700">{{ __('Security Question 1') }}</label>
                    <select id="question1" name="questions[0]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" required>
                        <option value="">Select a security question</option>
                        @foreach($securityQuestions as $question)
                            <option value="{{ $question->id }}" 
                                {{ isset($userAnswers[0]) && $userAnswers[0]->security_question_id == $question->id ? 'selected' : '' }}
                                data-question="{{ $question->question }}">
                                {{ $question->question }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="answer1" class="block font-medium text-sm text-gray-700">{{ __('Answer 1') }}</label>
                    <input id="answer1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" type="text" name="answers[0]" value="{{ isset($userAnswers[0]) ? $userAnswers[0]->answer : '' }}" required autocomplete="off" />
                </div>

                <div class="mt-4">
                    <label for="question2" class="block font-medium text-sm text-gray-700">{{ __('Security Question 2') }}</label>
                    <select id="question2" name="questions[1]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" required>
                        <option value="">Select a security question</option>
                        @foreach($securityQuestions as $question)
                            <option value="{{ $question->id }}" 
                                {{ isset($userAnswers[1]) && $userAnswers[1]->security_question_id == $question->id ? 'selected' : '' }}
                                data-question="{{ $question->question }}">
                                {{ $question->question }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="answer2" class="block font-medium text-sm text-gray-700">{{ __('Answer 2') }}</label>
                    <input id="answer2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" type="text" name="answers[1]" value="{{ isset($userAnswers[1]) ? $userAnswers[1]->answer : '' }}" required autocomplete="off" />
                </div>

                <div class="mt-4">
                    <label for="question3" class="block font-medium text-sm text-gray-700">{{ __('Security Question 3') }}</label>
                    <select id="question3" name="questions[2]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" required>
                        <option value="">Select a security question</option>
                        @foreach($securityQuestions as $question)
                            <option value="{{ $question->id }}" 
                                {{ isset($userAnswers[2]) && $userAnswers[2]->security_question_id == $question->id ? 'selected' : '' }}
                                data-question="{{ $question->question }}">
                                {{ $question->question }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="answer3" class="block font-medium text-sm text-gray-700">{{ __('Answer 3') }}</label>
                    <input id="answer3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" type="text" name="answers[2]" value="{{ isset($userAnswers[2]) ? $userAnswers[2]->answer : '' }}" required autocomplete="off" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ 
                        auth()->user()->hasRole('admin') ? route('admin.profile.edit') : 
                        (auth()->user()->hasRole('driver') ? route('driver.profile.edit') : 
                        (auth()->user()->hasRole('maintenance_staff') ? route('maintenance.profile.edit') : 
                        route('client.profile.edit'))) 
                    }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Back to Profile') }}
                    </a>

                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
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
