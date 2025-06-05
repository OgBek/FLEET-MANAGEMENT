@extends('layouts.dashboard')

@section('header')
    Pending Approvals
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-4 sm:p-6">
            <form action="{{ route('client.approvals.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Date Range Filter -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Requester Filter -->
                    <div>
                        <label for="requester_id" class="block text-sm font-medium text-gray-700">Requester</label>
                        <select id="requester_id" name="requester_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Staff</option>
                            @foreach($departmentStaff as $staff)
                                <option value="{{ $staff->id }}" {{ request('requester_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Filter Results
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Pending Booking Requests</h2>
            </div>

            @if($bookings->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending approvals</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no booking requests waiting for your approval.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->requestedBy->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->requestedBy->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $booking->vehicle->brand?->name }} {{ $booking->vehicle->model }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $booking->vehicle->registration_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $booking->start_time->format('M d, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ Str::limit($booking->purpose, 50) }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->destination }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="showApproveModal({{ $booking->id }})" 
                                                class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                        <button onclick="showRejectModal({{ $booking->id }})" 
                                                class="text-red-600 hover:text-red-900">Reject</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Booking Request</h3>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <p class="text-sm text-gray-500">Are you sure you want to approve this booking request?</p>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideApproveModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                Approve Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Booking Request</h3>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideRejectModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                Reject Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showApproveModal(bookingId) {
            document.getElementById('approveForm').action = "{{ url('client/approvals') }}/" + bookingId + "/approve";
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function hideApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            document.getElementById('approveForm').reset();
        }

        function showRejectModal(bookingId) {
            document.getElementById('rejectForm').action = "{{ url('client/approvals') }}/" + bookingId + "/reject";
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectForm').reset();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('bg-gray-500')) {
                event.target.classList.add('hidden');
            }
        }
    </script>
    @endpush
@endsection 