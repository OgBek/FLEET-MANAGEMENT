@props(['name', 'min' => null, 'max' => null, 'step' => 1])

<div
    x-data="{ 
        value: '',
        errorMessage: '',
        validateNumber(event) {
            const num = parseFloat(event.target.value);
            
            if (isNaN(num)) {
                this.errorMessage = 'Please enter a valid number';
                return false;
            }

            @if($min !== null)
            if (num < {{ $min }}) {
                this.errorMessage = 'Value must be at least {{ $min }}';
                return false;
            }
            @endif

            @if($max !== null)
            if (num > {{ $max }}) {
                this.errorMessage = 'Value must not exceed {{ $max }}';
                return false;
            }
            @endif

            this.errorMessage = '';
            return true;
        }
    }"
>
    <input
        type="number"
        name="{{ $name }}"
        id="{{ $name }}"
        step="{{ $step }}"
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        @input="validateNumber($event)"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500']) }}
    >

    <p 
        x-show="errorMessage"
        x-text="errorMessage"
        class="mt-1 text-sm text-red-600"
    ></p>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
