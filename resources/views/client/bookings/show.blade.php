@extends('layouts.dashboard')

@section('header')
    Booking Details
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Booking Status Banner -->
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 
                @if($booking->status === 'approved') bg-green-50
                @elseif($booking->status === 'pending') bg-yellow-50
                @elseif($booking->status === 'rejected') bg-red-50
                @else bg-gray-50 @endif">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Booking #{{ $booking->id }}
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Created on {{ $booking->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        @if($booking->status === 'approved') bg-green-100 text-green-800
                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                        <dd class="mt-1 text-sm">
                            @if($booking->vehicle)
                                <span class="text-gray-900">
                                    {{ $booking->vehicle->brand?->name ?? 'N/A' }} {{ $booking->vehicle->model ?? '' }}
                                </span>
                                @if($booking->vehicle->registration_number ?? false)
                                    <span class="text-gray-500 block mt-1">
                                        {{ $booking->vehicle->registration_number }}
                                    </span>
                                @endif
                            @else
                                <span class="text-red-500">Vehicle not found</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Driver</dt>
                        <dd class="mt-1 text-sm">
                            @if($booking->driver)
                                <span class="text-gray-900">{{ $booking->driver->name }}</span>
                                @if($booking->driver->phone)
                                    <span class="text-gray-500 block">
                                        {{ $booking->driver->phone }}
                                    </span>
                                @endif
                            @else
                                <span class="text-red-500">Driver not assigned</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->purpose }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Destination</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->destination }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $booking->start_time->format('M d, Y H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $booking->end_time->format('M d, Y H:i') }}
                        </dd>
                    </div>

                    @if($booking->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->notes }}</dd>
                        </div>
                    @endif

                    @if($booking->rejection_reason)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                            <dd class="mt-1 text-sm text-red-600">{{ $booking->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Action Buttons -->
            <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between">
                    <a href="{{ route('client.bookings.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Back to Bookings
                    </a>
                    
                    @if($booking->status === 'pending')
                        <div class="flex space-x-3">
                            <a href="{{ route('client.bookings.edit', $booking) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Edit Booking
                            </a>
                            <form action="{{ route('client.bookings.cancel', $booking) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Cancel Booking
                                </button>
                            </form>
                        </div>
                    @elseif($booking->status === 'cancelled')
                        <a href="{{ route('client.bookings.create', [
                            'vehicle_id' => $booking->vehicle_id,
                            'driver_id' => $booking->driver_id,
                            'purpose' => $booking->purpose,
                            'destination' => $booking->destination,
                            'pickup_location' => $booking->pickup_location,
                            'number_of_passengers' => $booking->number_of_passengers,
                            'notes' => $booking->notes
                        ]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Book Again
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 