@props([
    'type' => 'text',
    'name',
    'label',
    'value' => '',
    'required' => false,
    'pattern' => '',
    'helpText' => '',
    'options' => [],
    'minlength' => '',
    'maxlength' => '',
    'placeholder' => ''
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    @if ($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        >
            <option value="">Select {{ $label }}</option>
            @foreach($options as $key => $option)
                <option value="{{ $key }}" {{ $key == $value ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    @elseif ($type === 'email')
        <input 
            type="email" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            placeholder="{{ $placeholder ?: 'example@example.com' }}"
            title="Please enter a valid email address (e.g., example@example.com)"
        >
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
            {{ $pattern ? "pattern=$pattern" : '' }}
            {{ $minlength ? "minlength=$minlength" : '' }}
            {{ $maxlength ? "maxlength=$maxlength" : '' }}
            placeholder="{{ $placeholder }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        >
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @if ($helpText)
        <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
    @endif

    <div id="{{ $name }}-validation-message" class="mt-1 text-sm text-red-600 hidden"></div>
</div>

@once
@push('scripts')
<script>
    function showValidationMessage(inputId, message) {
        const messageDiv = document.getElementById(`${inputId}-validation-message`);
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.classList.remove('hidden');
        }
    }

    function hideValidationMessage(inputId) {
        const messageDiv = document.getElementById(`${inputId}-validation-message`);
        if (messageDiv) {
            messageDiv.classList.add('hidden');
        }
    }

    function validateInput(input) {
        if (input.validity.valueMissing) {
            showValidationMessage(input.id, `${input.labels[0].textContent.trim()} is required`);
        } else if (input.validity.typeMismatch) {
            showValidationMessage(input.id, `Please enter a valid ${input.type}`);
        } else if (input.validity.patternMismatch) {
            showValidationMessage(input.id, input.title || `Please match the requested format`);
        } else if (input.validity.rangeUnderflow) {
            showValidationMessage(input.id, `Minimum value is ${input.min}`);
        } else if (input.validity.rangeOverflow) {
            showValidationMessage(input.id, `Maximum value is ${input.max}`);
        } else if (input.validity.stepMismatch) {
            showValidationMessage(input.id, `Please enter a valid value. The step is ${input.step}`);
        } else {
            hideValidationMessage(input.id);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => validateInput(input));
                input.addEventListener('blur', () => validateInput(input));
            });
        });
    });
</script>
@endpush
@endonce
