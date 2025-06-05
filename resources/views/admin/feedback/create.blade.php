@extends('layouts.dashboard')

@section('header')
    Create Feedback
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Create New Feedback</h2>
                    <a href="{{ route('admin.feedback.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to List
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($bookings->isEmpty())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    There are no completed trips available for feedback. Feedback can only be provided for completed trips.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <form action="{{ route('admin.feedback.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="booking_id" class="block text-sm font-medium text-gray-700">Select Trip</label>
                            <select id="booking_id" name="booking_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">-- Select a completed trip --</option>
                                @foreach($bookings as $booking)
                                    <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                        {{ $booking->vehicle->registration_number }} - 
                                        {{ $booking->driver->name }} - 
                                        {{ $booking->start_time->format('M d, Y') }} to {{ $booking->end_time->format('M d, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Feedback Type</label>
                            <select id="type" name="type" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">-- Select feedback type --</option>
                                <option value="driver" {{ old('type') == 'driver' ? 'selected' : '' }}>Driver Performance</option>
                                <option value="vehicle" {{ old('type') == 'vehicle' ? 'selected' : '' }}>Vehicle Condition</option>
                                <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service Quality</option>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General Feedback</option>
                            </select>
                        </div>

                        <div>
                            <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <div class="flex rating-container">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="cursor-pointer star-label">
                                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only rating-input" {{ old('rating') == $i ? 'checked' : '' }}>
                                            <svg class="h-8 w-8 star-svg {{ old('rating') && old('rating') >= $i ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                fill="currentColor" 
                                                viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </label>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500 font-medium" id="rating-text">Excellent</span>
                            </div>
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Feedback Content</label>
                            <textarea id="content" name="content" rows="5" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('content') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Provide detailed feedback about the trip, driver, or vehicle.</p>
                        </div>

                        <div class="flex items-center">
                            <input id="is_public" name="is_public" type="checkbox" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('is_public') ? 'checked' : '' }}>
                            <label for="is_public" class="ml-2 block text-sm text-gray-900">
                                Make this feedback public (visible to all users)
                            </label>
                        </div>

                        <div class="pt-5">
                            <div class="flex justify-end">
                                <button type="button" onclick="window.location='{{ route('admin.feedback.index') }}'" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Submit Feedback
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ratingContainer = document.querySelector('.rating-container');
        const ratingInputs = document.querySelectorAll('.rating-input');
        const ratingText = document.getElementById('rating-text');
        const stars = document.querySelectorAll('.star-svg');
        const ratingTexts = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        
        function updateStars(selectedValue) {
            stars.forEach((star, index) => {
                if (index < selectedValue) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
            
            // Update rating text
            ratingText.textContent = ratingTexts[selectedValue - 1];
        }

        // Handle star hover effects
        stars.forEach((star, index) => {
            const label = star.parentElement;
            
            // Hover effects
            label.addEventListener('mouseenter', () => {
                updateStars(index + 1);
            });
            
            // Mouse leave - restore selected rating
            label.addEventListener('mouseleave', () => {
                const selectedRating = document.querySelector('.rating-input:checked');
                if (selectedRating) {
                    updateStars(parseInt(selectedRating.value));
                } else {
                    updateStars(5); // Default to 5 stars
                }
            });

            // Click handler
            label.addEventListener('click', () => {
                const input = label.querySelector('.rating-input');
                input.checked = true;
                updateStars(index + 1);
            });
        });

        // Handle direct radio input changes
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                updateStars(parseInt(this.value));
            });
        });

        // Initialize with default or selected value
        window.addEventListener('load', () => {
            const selectedRating = document.querySelector('.rating-input:checked');
            if (!selectedRating) {
                // Default to 5 stars if no rating is selected
                ratingInputs[4].checked = true;
                updateStars(5);
            } else {
                updateStars(parseInt(selectedRating.value));
            }
        });
    });
</script>
@endpush
@endsection
