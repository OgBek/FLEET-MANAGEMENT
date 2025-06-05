<!-- Profile Photo -->
<div class="flex items-center space-x-8">
    <!-- Current Profile Photo -->
    <div class="flex-shrink-0">
        <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="rounded-full h-20 w-20 object-cover">
    </div>

    <div class="flex flex-col space-y-4">
        <div class="flex items-center space-x-4">
            <label for="photo" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 cursor-pointer">
                {{ __('Select New Photo') }}
                <input type="file" name="photo" id="photo" class="hidden" accept="image/*">
            </label>

            @if(auth()->user()->image_data)
                <input type="hidden" name="delete_photo" id="delete_photo" value="0">
                <button type="button" onclick="document.getElementById('delete_photo').value='1'; this.form.submit();" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50">
                    {{ __('Remove Photo') }}
                </button>
            @endif
        </div>

        <!-- New Photo Preview -->
        <div id="preview-container" class="hidden">
            <img id="preview-image" class="rounded-full h-20 w-20 object-cover" src="" alt="Preview">
        </div>

        <input type="hidden" name="image" id="image-data">

        @error('photo')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const imageData = document.getElementById('image-data');

        photoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    imageData.value = e.target.result;
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>
@endpush 