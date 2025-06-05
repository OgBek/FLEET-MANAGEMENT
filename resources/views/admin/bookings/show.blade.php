@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Booking Details</h1>
        <a href="{{ route('admin.bookings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Status Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Status</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <span class="text-gray-700 font-medium">Current Status:</span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium text-white {{ 
                        $booking->status === 'approved' ? 'bg-green-500' : 
                        ($booking->status === 'rejected' ? 'bg-red-500' : 
                        ($booking->status === 'in_progress' ? 'bg-blue-500' : 'bg-yellow-500')) 
                    }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>

                @if($booking->status === 'approved')
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="mb-2">
                        <span class="text-gray-700 font-medium">Approved By:</span>
                        <span class="text-gray-600">{{ $booking->approvedBy->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-700 font-medium">Approved At:</span>
                        <span class="text-gray-600">{{ $booking->approved_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
                @endif

                @if($booking->status === 'rejected' && $booking->rejection_reason)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="font-medium text-red-600 mb-2">Rejection Reason:</h4>
                    <p class="text-gray-600 bg-red-50 p-3 rounded">{{ $booking->rejection_reason }}</p>
                </div>
                @endif

                @if($booking->status === 'pending')
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex flex-col space-y-3">
                        <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Approve Booking
                            </button>
                        </form>

                        <button type="button" 
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center"
                            onclick="showRejectModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Reject Booking
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Vehicle Info Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Vehicle Information</h2>
            </div>
            <div class="p-6">
                @if($booking->vehicle)
                    <div class="space-y-4">
                        <div>
                            <span class="text-gray-700 font-medium">Vehicle:</span>
                            <div class="mt-1 text-gray-600">
                                {{ $booking->vehicle->brand->name ?? 'N/A' }} {{ $booking->vehicle->model ?? '' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Type:</span>
                            <div class="mt-1 text-gray-600">
                                {{ $booking->vehicle->type->category->name ?? 'N/A' }} ({{ $booking->vehicle->type->name ?? 'N/A' }})
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Registration Number:</span>
                            <div class="mt-1 text-gray-600 font-mono bg-gray-50 px-3 py-1 rounded">
                                {{ $booking->vehicle->registration_number ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Vehicle no longer available</h3>
                        <p class="mt-1 text-sm text-gray-500">This vehicle has been removed from the system.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Driver Info Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Driver Information</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <span class="text-gray-700 font-medium">Name:</span>
                        <div class="mt-1 text-gray-600">{{ $booking->driver->name }}</div>
                    </div>
                    <div>
                        <span class="text-gray-700 font-medium">Phone:</span>
                        <div class="mt-1 text-gray-600">
                            <a href="tel:{{ $booking->driver->phone }}" class="text-blue-600 hover:underline">
                                {{ $booking->driver->phone }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-700 font-medium">Email:</span>
                        <div class="mt-1 text-gray-600">
                            <a href="mailto:{{ $booking->driver->email }}" class="text-blue-600 hover:underline">
                                {{ $booking->driver->email }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Details Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 mb-6">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Trip Details</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-4">
                        <div>
                            <span class="text-gray-700 font-medium">Department:</span>
                            <div class="mt-1 text-gray-600">{{ $booking->department->name }}</div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Start Time:</span>
                            <div class="mt-1 text-gray-600 bg-blue-50 px-3 py-1 rounded inline-block">
                                {{ $booking->start_time->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">End Time:</span>
                            <div class="mt-1 text-gray-600 bg-blue-50 px-3 py-1 rounded inline-block">
                                {{ $booking->end_time->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="space-y-4">
                        <div>
                            <span class="text-gray-700 font-medium">Pickup Location:</span>
                            <div class="mt-1 text-gray-600">{{ $booking->pickup_location }}</div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Destination:</span>
                            <div class="mt-1 text-gray-600">{{ $booking->destination }}</div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Number of Passengers:</span>
                            <div class="mt-1 text-gray-600">
                                <span class="bg-gray-100 px-3 py-1 rounded">{{ $booking->number_of_passengers }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purpose & Requester Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Purpose & Request Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="text-gray-700 font-medium">Purpose:</span>
                    <div class="mt-2 bg-gray-50 p-4 rounded-md text-gray-600 min-h-[100px]">
                        {{ $booking->purpose }}
                    </div>
                </div>
                <div>
                    <div class="space-y-4">
                        <div>
                            <span class="text-gray-700 font-medium">Requested By:</span>
                            <div class="mt-1 text-gray-600">{{ $booking->requestedBy->name }}</div>
                        </div>
                        <div>
                            <span class="text-gray-700 font-medium">Request Date:</span>
                            <div class="mt-1 text-gray-600">{{ $booking->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mb-4 border-b pb-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Reject Booking</h3>
            <button type="button" onclick="hideRejectModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="rejectForm" action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Rejection <span class="text-red-500">*</span>
                    <span id="charCount" class="text-xs text-gray-500 float-right">0/255</span>
                </label>
                <textarea name="rejection_reason" id="rejection_reason" rows="4" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required maxlength="255" 
                    placeholder="Please provide a detailed reason for rejecting this booking request..."></textarea>
                <p id="error-message" class="mt-1 text-sm text-red-600 hidden">Please provide a reason for rejection (max 255 characters)</p>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="hideRejectModal()" 
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <svg id="submitSpinner" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submitText">Confirm Rejection</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 

@push('scripts')
<script>
    function showRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        document.getElementById('rejection_reason').focus();
    }

    function hideRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    document.addEventListener('DOMContentLoaded', function() {
        const rejectForm = document.getElementById('rejectForm');
        const rejectionReason = document.getElementById('rejection_reason');
        const charCount = document.getElementById('charCount');
        const errorMessage = document.getElementById('error-message');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        let isSubmitting = false;

        // Update character count
        rejectionReason.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = `${currentLength}/255`;
            
            // Toggle error message
            if (currentLength === 0) {
                errorMessage.classList.remove('hidden');
                submitBtn.disabled = true;
            } else {
                errorMessage.classList.add('hidden');
                submitBtn.disabled = isSubmitting;
            }
            
            // Visual feedback for character limit
            if (currentLength > 200) {
                charCount.classList.add('text-yellow-600', 'font-medium');
            } else {
                charCount.classList.remove('text-yellow-600', 'font-medium');
            }
        });

        // Form submission
        rejectForm.addEventListener('submit', function(e) {
            const reason = rejectionReason.value.trim();
            
            if (reason === '') {
                e.preventDefault();
                errorMessage.classList.remove('hidden');
                rejectionReason.focus();
                return false;
            }
            
            if (!isSubmitting) {
                isSubmitting = true;
                submitBtn.disabled = true;
                submitText.textContent = 'Processing...';
                submitSpinner.classList.remove('hidden');
                
                // Optional: Add a small delay to show the loading state
                setTimeout(() => {
                    this.submit();
                }, 500);
            } else {
                e.preventDefault();
                return false;
            }
        });

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRejectModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRejectModal();
            }
        });
    });
</script>
@endpush