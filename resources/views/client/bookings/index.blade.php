@extends('layouts.dashboard')

@section('header')
    Vehicle Bookings
@endsection

@section('content')
    <!-- Statistics -->
    <div class="mb-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_bookings'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending_bookings'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Approved Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['approved_bookings'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $stats['this_month_bookings'] }}</dd>
                </div>
            </div>
        </dl>
    </div>

    <!-- Bookings List -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">My Bookings</h2>
                <a href="{{ route('client.bookings.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    New Booking
                </a>
            </div>

            @if($bookings->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new booking.</p>
                    <div class="mt-6">
                        <a href="{{ route('client.bookings.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create New Booking
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($booking->vehicle)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $booking->vehicle->registration_number }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $booking->vehicle->brand?->name ?? 'N/A' }} {{ $booking->vehicle->model ?? '' }}
                                            </div>
                                        @else
                                            <div class="text-sm font-medium text-red-500">
                                                Vehicle not found
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($booking->driver)
                                            <div class="text-sm text-gray-900">{{ $booking->driver->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $booking->driver->phone ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-sm text-red-500">Driver not assigned</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $booking->start_time->format('M d, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($booking->status === 'approved') bg-green-100 text-green-800
                                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('client.bookings.show', $booking) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        
                                        @if($booking->status === 'pending')
                                            <a href="{{ route('client.bookings.edit', $booking) }}" 
                                               class="ml-3 text-indigo-600 hover:text-indigo-900">Edit</a>
                                            
                                            <form action="{{ route('client.bookings.cancel', $booking) }}" 
                                                  method="POST" 
                                                  class="inline ml-3"
                                                  onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                                            </form>
                                        @endif
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
@endsection 