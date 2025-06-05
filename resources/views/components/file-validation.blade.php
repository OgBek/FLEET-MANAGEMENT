@props(['name', 'accept' => '', 'maxSize' => 5])

<div class="w-full">
    <label class="block text-sm font-medium text-gray-700">{{ ucfirst($name) }}</label>
    <div class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
        <div class="space-y-1 text-center">
            <div class="flex flex-col items-center">
                <img id="preview-{{ $name }}" class="h-40 w-40 object-cover mb-4 hidden">
                <label for="{{ $name }}-input" 
                       class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Select File
                </label>
            </div>
            <input type="file" 
                   id="{{ $name }}-input" 
                   name="{{ $name }}" 
                   class="hidden" 
                   accept="{{ $accept }}"
                   @change="validateFile($event)">
            <p class="text-xs text-gray-500">
                @if($accept)
                    Accepted formats: {{ str_replace('/*', '', $accept) }}
                @endif
                (Max {{ $maxSize }}MB)
            </p>
            <div id="error-{{ $name }}" class="mt-1 text-sm text-red-600 hidden"></div>
            @error($name)
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('{{ $name }}-input');
        const preview = document.getElementById('preview-{{ $name }}');
        const error = document.getElementById('error-{{ $name }}');

        function validateFile(event) {
            const file = event.target.files[0];
            error.classList.add('hidden');
            preview.classList.add('hidden');

            if (!file) return;

            // Check file size
            if (file.size > {{ $maxSize }} * 1024 * 1024) {
                error.textContent = `File size must not exceed {{ $maxSize }}MB`;
                error.classList.remove('hidden');
                event.target.value = '';
                return;
            }

            // Check file type if specified
            if ('{{ $accept }}' && !file.type.match('{{ $accept }}')) {
                error.textContent = `Please upload a valid file type ({{ str_replace('/*', '', $accept) }})`;
                error.classList.remove('hidden');
                event.target.value = '';
                return;
            }

            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        input.addEventListener('change', validateFile);
    });
</script>
