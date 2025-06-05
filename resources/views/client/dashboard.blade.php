@extends('layouts.dashboard')

@section('header')
    Department Dashboard
@endsection

@section('navigation')
    <a href="{{ route('client.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
        Dashboard
    </a>
    <a href="{{ route('client.bookings.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        My Bookings
    </a>
    <a href="{{ route('client.vehicles.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Available Vehicles
    </a>
    <a href="{{ route('client.feedback.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Feedback
    </a>
    @if(auth()->user()->hasRole('department_head'))
    <a href="{{ route('client.approvals.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Pending Approvals
    </a>
    @endif
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            @if(auth()->user()->hasRole('driver'))
                <div class="flex items-center">
                    <span class="mr-2">Availability Status:</span>
                    <form action="{{ route('client.drivers.toggle-availability') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 rounded-md {{ auth()->user()->is_available ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white">
                            {{ auth()->user()->is_available ? 'Mark as Unavailable' : 'Mark as Available' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(auth()->user()->hasRole('driver'))
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">My Assigned Trips</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2">Requester</th>
                                <th class="px-4 py-2">Vehicle</th>
                                <th class="px-4 py-2">Start Time</th>
                                <th class="px-4 py-2">End Time</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedBookings as $booking)
                                <tr id="booking-row-{{ $booking->id }}">
                                    <td class="border px-4 py-2">{{ $booking->requester->name }}</td>
                                    <td class="border px-4 py-2">{{ $booking->vehicle->registration_number }}</td>
                                    <td class="border px-4 py-2">{{ $booking->start_time->format('M d, Y H:i') }}</td>
                                    <td class="border px-4 py-2">{{ $booking->end_time->format('M d, Y H:i') }}</td>
                                    <td class="border px-4 py-2">
                                        <span class="booking-status px-2 py-1 rounded-full text-sm
                                            @if($booking->status === 'approved') bg-blue-100 text-blue-800
                                            @elseif($booking->status === 'in_progress') bg-yellow-100 text-yellow-800
                                            @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                            @endif">
                                            @if($booking->status === 'approved')
                                                Ready for Pickup
                                            @elseif($booking->status === 'in_progress')
                                                In Progress
                                            @elseif($booking->status === 'completed')
                                                Completed
                                            @else
                                                {{ ucfirst($booking->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('driver.trips.show', $booking) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                            
                                            @if($booking->status === 'approved')
                                                <button onclick="startTrip({{ $booking->id }})" 
                                                        class="start-trip-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                                    Start Trip
                                                </button>
                                            @elseif($booking->status === 'in_progress')
                                                <button onclick="completeTrip({{ $booking->id }})" 
                                                        class="complete-trip-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">
                                                    Drop Off
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('client.bookings.create') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">New Booking</h3>
                                <p class="text-sm text-gray-500">Request a vehicle for your department</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('client.bookings.index') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">View Bookings</h3>
                                <p class="text-sm text-gray-500">Check your booking history</p>
                            </div>
                        </div>
                    </div>
                </a>

                @if(auth()->user()->hasRole('department_head'))
                <a href="{{ route('client.approvals.index') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Pending Approvals</h3>
                                <p class="text-sm text-gray-500">Review department booking requests</p>
                            </div>
                        </div>
                    </div>
                </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- My Recent Bookings -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Recent Bookings</h3>
                </div>
                <div class="p-5">
                    <div class="flow-root">
                        <ul class="-my-5 divide-y divide-gray-200">
                            @forelse($recentBookings as $booking)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                @if($booking->vehicle)
                                                    {{ $booking->vehicle->registration_number }} - {{ $booking->vehicle->model }}
                                                @else
                                                    <span class="text-red-500">Vehicle not available</span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $booking->start_time->format('M d, Y H:i') }} - {{ $booking->end_time->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($booking->status === 'approved') bg-green-100 text-green-800
                                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-4">
                                    <div class="text-center text-gray-500">No recent bookings</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- My Recent Feedback -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Recent Feedback</h3>
                    <a href="{{ route('client.feedback.create') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        New Feedback
                    </a>
                </div>
                <div class="p-5">
                    @if($recentFeedback && $recentFeedback->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentFeedback as $feedback)
                                <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            @if(auth()->user()->hasRole('department_head'))
                                                <p class="text-xs text-gray-500 mb-1">{{ $feedback->user->name }}</p>
                                            @endif
                                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($feedback->type) }}</p>
                                            <p class="text-sm text-gray-500">{{ Str::limit($feedback->content, 100) }}</p>
                                        </div>
                                        @if($feedback->rating)
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $feedback->created_at->format('M d, Y') }}
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $feedback->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $feedback->is_approved ? 'Approved' : 'Pending Approval' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('client.feedback.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View all feedback →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500">No feedback submitted yet.</p>
                            <a href="{{ route('client.feedback.create') }}" class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                Submit your first feedback →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Vehicles -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Available Vehicles</h3>
                </div>
                <div class="p-5">
                    <div class="flow-root">
                        <ul class="-my-5 divide-y divide-gray-200">
                            @forelse($availableVehicles as $vehicle)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ $vehicle->brand->name }} {{ $vehicle->model }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $vehicle->type->name }} • {{ $vehicle->registration_number }}
                                            </p>
                                        </div>
                                        <div>
                                            <a href="{{ route('client.bookings.create', ['vehicle' => $vehicle->id]) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                Book Now
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-4">
                                    <div class="text-center text-gray-500">No vehicles available</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('department_head'))
            <!-- Department Statistics -->
            <div class="col-span-1 lg:col-span-2 bg-white shadow rounded-lg">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Department Statistics</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Department Bookings</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $departmentStats['total_bookings'] }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Approvals</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $departmentStats['pending_approvals'] }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Bookings</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $departmentStats['active_bookings'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function updateBookingRow(booking) {
            const row = document.getElementById(`booking-row-${booking.id}`);
            const statusSpan = row.querySelector('.booking-status');
            const actionsDiv = row.querySelector('.flex.items-center');

            // Update status display
            let statusText = '';
            let statusClasses = 'px-2 py-1 rounded-full text-sm ';
            
            if (booking.status === 'approved') {
                statusText = 'Ready for Pickup';
                statusClasses += 'bg-blue-100 text-blue-800';
            } else if (booking.status === 'in_progress') {
                statusText = 'In Progress';
                statusClasses += 'bg-yellow-100 text-yellow-800';
            } else if (booking.status === 'completed') {
                statusText = 'Completed';
                statusClasses += 'bg-green-100 text-green-800';
            }

            statusSpan.textContent = statusText;
            statusSpan.className = 'booking-status ' + statusClasses;

            // Update action buttons
            let actionButton = '';
            if (booking.status === 'approved') {
                actionButton = `<button onclick="startTrip(${booking.id})" 
                                      class="start-trip-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                  Start Trip
                              </button>`;
            } else if (booking.status === 'in_progress') {
                actionButton = `<button onclick="completeTrip(${booking.id})" 
                                      class="complete-trip-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">
                                  Drop Off
                              </button>`;
            }

            const viewDetailsLink = actionsDiv.querySelector('a').outerHTML;
            actionsDiv.innerHTML = viewDetailsLink + (actionButton ? `<div class="ml-3">${actionButton}</div>` : '');
        }

        function startTrip(bookingId) {
            fetch(`/driver/trips/${bookingId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBookingRow(data.booking);
                    // Show success message
                    alert('Trip started successfully');
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start trip');
            });
        }

        function completeTrip(bookingId) {
            fetch(`/driver/trips/${bookingId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBookingRow(data.booking);
                    // Show success message
                    alert('Trip completed successfully');
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to complete trip');
            });
        }
    </script>
    @endpush
@endsection 