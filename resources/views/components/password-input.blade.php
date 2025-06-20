@props([
    'id' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => '••••••••',
    'required' => true,
    'minlength' => 8,
    'autocomplete' => 'new-password'
])

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="mt-1 relative rounded-md shadow-sm">
        <input 
            id="{{ $id }}" 
            name="{{ $name }}" 
            type="password" 
            {{ $required ? 'required' : '' }}
            minlength="{{ $minlength }}"
            autocomplete="{{ $autocomplete }}"
            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error($name) border-red-500 @enderror"
            placeholder="{{ $placeholder }}"
        >
        <button type="button" onclick="togglePassword('{{ $id }}')" class="absolute inset-y-0 right-0 pr-3 flex items-center focus:outline-none">
            <svg id="{{ $id }}-icon-show" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg id="{{ $id }}-icon-hide" class="h-5 w-5 text-gray-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
        <script>
            function togglePassword(inputId) {
                const input = document.getElementById(inputId);
                const iconShow = document.getElementById(`${inputId}-icon-show`);
                const iconHide = document.getElementById(`${inputId}-icon-hide`);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    iconShow.classList.add('hidden');
                    iconHide.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    iconShow.classList.remove('hidden');
                    iconHide.classList.add('hidden');
                }
            }
        </script>
    </div>
    @error($name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
