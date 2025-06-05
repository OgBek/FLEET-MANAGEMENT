@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Bookings</h2>
                <a href="{{ route('admin.bookings.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Booking
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-4 sm:p-6 border-b border-gray-200">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                        <select id="department_id" name="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by requester name or vehicle registration..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    @if(request()->hasAny(['status', 'department_id', 'start_date', 'end_date', 'search']))
                        <a href="{{ route('admin.bookings.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Clear Filters
                        </a>
                    @endif
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Booking Details
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vehicle & Driver
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $booking->requestedBy->name }}</div>
                                    <div class="text-gray-500">
                                        {{ $booking->start_time->format('M d, Y H:i') }} - {{ $booking->end_time->format('M d, Y H:i') }}
                                    </div>
                                    <div class="text-gray-500">{{ Str::limit($booking->purpose, 50) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->vehicle)
                                    <div class="text-sm text-gray-900">
                                        {{ $booking->vehicle->brand->name ?? 'N/A' }} {{ $booking->vehicle->model ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $booking->vehicle->type->category->name ?? 'N/A' }} ({{ $booking->vehicle->type->name ?? '' }})
                                    </div>
                                @else
                                    <div class="text-sm text-gray-900 italic">
                                        Vehicle no longer available
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        This vehicle has been removed from the system
                                    </div>
                                @endif
                                <div class="text-sm text-gray-900 mt-2">Driver: {{ $booking->driver->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">Phone: {{ $booking->driver->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $booking->department->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($booking->status === 'approved') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    <a href="{{ route('admin.bookings.edit', $booking) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    @if($booking->status === 'pending')
                                        <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                        </form>
                                        <button type="button" 
                                            class="text-red-600 hover:text-red-900"
                                            onclick="showRejectModal('{{ $booking->id }}')">
                                            Reject
                                        </button>
                                    @endif
                                    <form action="{{ route('admin.bookings.destroy', $booking) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No bookings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
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
        <form id="rejectForm" action="" method="POST">
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
@endpush

@push('scripts')
<script>
    function showRejectModal(bookingId) {
        const form = document.getElementById('rejectForm');
        // Create the route URL with the booking ID
        const route = '{{ route('admin.bookings.reject', ['booking' => 'BOOKING_ID']) }}';
        form.action = route.replace('BOOKING_ID', bookingId);
        console.log('Form action set to:', form.action);  // Debug log
        document.getElementById('rejectModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('rejection_reason').focus();
    }

    function hideRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        const form = document.getElementById('rejectForm');
        form.reset();
        document.getElementById('charCount').textContent = '0/255';
        document.getElementById('error-message').classList.add('hidden');
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitText').textContent = 'Confirm Rejection';
        document.getElementById('submitSpinner').classList.add('hidden');
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
            e.preventDefault();
            const reason = rejectionReason.value.trim();
            
            if (reason === '') {
                errorMessage.classList.remove('hidden');
                rejectionReason.focus();
                return false;
            }
            
            if (!isSubmitting) {
                isSubmitting = true;
                submitBtn.disabled = true;
                submitText.textContent = 'Processing...';
                submitSpinner.classList.remove('hidden');
                
                // Submit the form
                this.submit();
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