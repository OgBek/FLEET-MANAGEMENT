@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-700 text-3xl font-medium">Profile Settings</h3>
    </div>

    <div class="space-y-6">
        <!-- Profile Information -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <section>
                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">{{ __('Profile Information') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __("Update your account's profile information and contact details.") }}</p>
                    </header>

                    <form method="post" action="{{ route('client.profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <!-- Profile Photo -->
                        <div class="flex flex-col items-center space-y-4">
                            <!-- Current Profile Photo -->
                            <div class="mt-2">
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="rounded-full h-32 w-32 object-cover">
                            </div>

                            <div class="flex items-center space-x-4">
                                <label for="photo" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 cursor-pointer">
                                    {{ __('Select New Photo') }}
                                    <input type="file" name="photo" id="photo" class="hidden" accept="image/*">
                                </label>

                                @if(auth()->user()->profile_photo_path)
                                    <form method="POST" action="{{ route('client.profile.photo.destroy') }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50">
                                            {{ __('Remove Photo') }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- New Photo Preview -->
                            <div id="preview-container" class="mt-2 hidden">
                                <img id="preview-image" class="rounded-full h-32 w-32 object-cover" src="" alt="Preview">
                            </div>

                            @error('photo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 mt-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        <!-- Security Questions -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.security-questions')
            </div>
        </div>

        <!-- Password Update Form -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>

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
@endsection