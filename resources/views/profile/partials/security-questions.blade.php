<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Security Questions') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your security questions and answers for account recovery.') }}
        </p>
    </header>

    <div class="mt-6">
        @php
            $userAnswers = auth()->user()->securityAnswers()->with('securityQuestion')->get();
        @endphp

        @if($userAnswers->count() > 0)
            @foreach($userAnswers as $answer)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700">{{ $answer->securityQuestion->question }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Answer is set <span class="text-gray-400">(hidden for security)</span></p>
                </div>
            @endforeach
        @else
            <div class="text-sm text-gray-600">
                {{ __('No security questions have been set up yet. Please set up your security questions to help protect your account.') }}
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ 
                auth()->user()->hasRole('admin') ? route('admin.profile.security-questions.update') : 
                (auth()->user()->hasRole('driver') ? route('driver.profile.security-questions.update') : 
                (auth()->user()->hasRole('maintenance_staff') ? route('maintenance.profile.security-questions.update') : 
                (auth()->user()->hasRole(['department_head', 'department_staff']) ? route('client.profile.security-questions.update') : 
                route('client.profile.security-questions.update')))) 
            }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Manage Security Questions') }}
            </a>
        </div>
    </div>
</section>
