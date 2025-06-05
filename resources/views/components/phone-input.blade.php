@props(['name', 'label', 'value' => '', 'required' => false])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="mt-1 flex rounded-md shadow-sm">
        <span class="inline-flex items-center px-4 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
            +251
        </span>
        <input 
            type="tel" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            pattern="^9[0-9]{8}$"
            maxlength="9"
            placeholder="912345678"
            title="Please enter a valid Ethiopian phone number starting with 9 (9 digits total)"
            x-data
            x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').substring(0, 9)"
        >
    </div>
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-xs text-gray-500">Enter 9 digits starting with 9 (e.g., 912345678)</p>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            this.value = value;

            // Validate the number format
            if (value.length === 9) {
                if (!value.startsWith('9')) {
                    this.setCustomValidity('Phone number must start with 9');
                } else {
                    this.setCustomValidity('');
                }
            } else {
                this.setCustomValidity('Phone number must be exactly 9 digits');
            }
        });
    });
});
</script>
@endpush
@endonce
