@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Share Your Feedback</h2>
                    <a href="{{ route('feedback.index') }}" class="text-blue-600 hover:text-blue-800">Back to Feedbacks</a>
                </div>

                <form action="{{ route('feedback.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Feedback Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Feedback Type</label>
                        <select name="type" id="type" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="booking" {{ old('type') == 'booking' ? 'selected' : '' }}>Booking Experience</option>
                            <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service Quality</option>
                            <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General Feedback</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Related Booking -->
                    <div id="bookingField" style="display: none;">
                        <label for="booking_id" class="block text-sm font-medium text-gray-700">Related Booking</label>
                        <select name="booking_id" id="booking_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Booking</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                    Booking #{{ $booking->id }} - {{ $booking->vehicle->registration_number }} ({{ $booking->start_date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rating</label>
                        <div class="mt-2 flex items-center space-x-4">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center">
                                    <input type="radio" name="rating" value="{{ $i }}" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                           {{ old('rating') == $i ? 'checked' : '' }}
                                           required>
                                    <span class="ml-2 text-sm text-gray-600">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</span>
                                </label>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Feedback Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Your Feedback</label>
                        <textarea id="content" name="content" rows="4" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Share your experience...">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Make Public -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_public" name="is_public" type="checkbox" value="1"
                                   {{ old('is_public') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_public" class="font-medium text-gray-700">Make this feedback public</label>
                            <p class="text-gray-500">Your feedback may be shown on our testimonials page</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        const bookingField = document.getElementById('bookingField');
        if (this.value === 'booking') {
            bookingField.style.display = 'block';
            document.getElementById('booking_id').required = true;
        } else {
            bookingField.style.display = 'none';
            document.getElementById('booking_id').required = false;
        }
    });

    // Trigger on page load if booking type is selected
    if (document.getElementById('type').value === 'booking') {
        document.getElementById('bookingField').style.display = 'block';
        document.getElementById('booking_id').required = true;
    }
</script>
@endpush
@endsection
