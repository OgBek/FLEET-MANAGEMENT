@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Edit Feedback</h2>
                    <p class="mt-1 text-sm text-gray-600">Update your feedback details below.</p>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('client.feedback.update', $feedback) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Feedback Type</label>
                        <select id="type" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="booking" {{ $feedback->type == 'booking' ? 'selected' : '' }}>Booking Experience</option>
                            <option value="service" {{ $feedback->type == 'service' ? 'selected' : '' }}>Service Quality</option>
                            <option value="general" {{ $feedback->type == 'general' ? 'selected' : '' }}>General Feedback</option>
                        </select>
                    </div>

                    @if($bookings->count() > 0)
                        <div>
                            <label for="booking_id" class="block text-sm font-medium text-gray-700">Related Booking (Optional)</label>
                            <select id="booking_id" name="booking_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select a booking</option>
                                @foreach($bookings as $booking)
                                    <option value="{{ $booking->id }}" {{ $feedback->booking_id == $booking->id ? 'selected' : '' }}>
                                        Booking #{{ $booking->id }} - {{ $booking->vehicle->registration_number }}
                                        ({{ $booking->start_time->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                        <div class="mt-1 flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <label for="rating{{ $i }}" class="cursor-pointer">
                                    <input type="radio" id="rating{{ $i }}" name="rating" value="{{ $i }}" class="hidden peer" {{ $feedback->rating == $i ? 'checked' : '' }}>
                                    <svg class="h-8 w-8 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300' }} peer-checked:text-yellow-400 hover:text-yellow-400 cursor-pointer" 
                                         fill="currentColor" 
                                         viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Your Feedback</label>
                        <div class="mt-1">
                            <textarea id="content" 
                                      name="content" 
                                      rows="4" 
                                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                      placeholder="Share your experience...">{{ old('content', $feedback->content) }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Minimum 10 characters required.</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_public" 
                               name="is_public" 
                               value="1" 
                               {{ $feedback->is_public ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-900">
                            Make this feedback public (visible to other users)
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('client.feedback.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Update Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating hover effect
    const stars = document.querySelectorAll('[id^="rating"]');
    stars.forEach((star, index) => {
        star.addEventListener('change', () => {
            stars.forEach((s, i) => {
                s.parentElement.querySelector('svg').classList.toggle('text-yellow-400', i <= index);
                s.parentElement.querySelector('svg').classList.toggle('text-gray-300', i > index);
            });
        });
    });

    // Type selection affects booking visibility
    const typeSelect = document.getElementById('type');
    const bookingSelect = document.getElementById('booking_id')?.parentElement;

    if (typeSelect && bookingSelect) {
        typeSelect.addEventListener('change', () => {
            bookingSelect.style.display = typeSelect.value === 'booking' ? 'block' : 'none';
        });
        
        // Initial state
        bookingSelect.style.display = typeSelect.value === 'booking' ? 'block' : 'none';
    }
});
</script>
@endpush
@endsection
