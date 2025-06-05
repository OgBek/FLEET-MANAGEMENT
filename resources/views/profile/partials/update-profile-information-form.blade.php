<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form method="post" action="{{ route('client.profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

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

                    @if(auth()->user()->profile_photo_path)
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

                @error('photo')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            @error('name')
                <x-input-error :messages="$message" class="mt-2" />
            @enderror
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" :value="old('email', $user->email)" required autocomplete="username" />
            @error('email')
                <x-input-error :messages="$message" class="mt-2" />
            @enderror
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                    +251
                </span>
                <x-text-input 
                    id="phone" 
                    name="phone" 
                    type="tel" 
                    class="pl-16 mt-0 block w-full border-gray-300 rounded-md shadow-sm" 
                    :value="old('phone', $user->phone)" 
                    required 
                    autocomplete="tel"
                    pattern="^9[0-9]{8}$"
                    title="Please enter a valid Ethiopian phone number starting with 9 followed by 8 digits"
                />
            </div>
            @error('phone')
                <x-input-error :messages="$message" class="mt-2" />
            @else
                <p class="mt-1 text-xs text-gray-500">Enter your 9-digit Ethiopian phone number starting with 9</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');

        photoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>
@endpush
