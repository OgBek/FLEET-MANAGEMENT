@extends('layouts.dashboard')

@section('header')
    Vehicle Availability Calendar
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Filters -->
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <form action="{{ route('client.vehicles.calendar') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="vehicle_type" class="block text-sm font-medium text-gray-700">Vehicle Type</label>
                <select id="vehicle_type" name="vehicle_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($vehicleTypes as $type)
                        <option value="{{ $type->id }}" {{ request('vehicle_type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Month</label>
                <input type="month" id="date" name="date" 
                       value="{{ request('date', now()->format('Y-m')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Calendar
                </button>
            </div>
        </form>
    </div>

    <!-- Calendar -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $currentDate->format('F Y') }}
                </h2>
                <div class="flex space-x-2">
                    <a href="{{ route('client.vehicles.calendar', ['date' => $currentDate->copy()->subMonth()->format('Y-m')]) }}"
                       class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous Month
                    </a>
                    <a href="{{ route('client.vehicles.calendar', ['date' => $currentDate->copy()->addMonth()->format('Y-m')]) }}"
                       class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next Month
                    </a>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                <!-- Day Headers -->
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayHeader)
                    <div class="bg-gray-50 py-2 text-center text-sm font-medium text-gray-500">
                        {{ $dayHeader }}
                    </div>
                @endforeach

                <!-- Calendar Days -->
                @foreach($calendar as $day)
                    <div class="min-h-[120px] bg-white p-2 {{ $day['isToday'] ? 'bg-blue-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <span class="text-sm {{ $day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $day['date']->format('j') }}
                            </span>
                            @if($day['isToday'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Today
                                </span>
                            @endif
                        </div>

                        <!-- Vehicle Bookings -->
                        <div class="mt-2 space-y-1">
                            @foreach($day['bookings'] as $booking)
                                <div class="px-2 py-1 text-xs rounded-md 
                                    @if($booking->status === 'approved') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $booking->vehicle->registration_number }}
                                    <br>
                                    {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Legend -->
            <div class="mt-4 flex items-center space-x-4 text-sm">
                <div class="flex items-center">
                    <span class="w-3 h-3 inline-block rounded-full bg-green-100 mr-1"></span>
                    <span class="text-gray-600">Approved Bookings</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 inline-block rounded-full bg-yellow-100 mr-1"></span>
                    <span class="text-gray-600">Pending Bookings</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 inline-block rounded-full bg-blue-50 mr-1"></span>
                    <span class="text-gray-600">Today</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 flex justify-end">
        <a href="{{ route('client.bookings.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Booking
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle date changes
        const dateInput = document.getElementById('date');
        dateInput.addEventListener('change', function() {
            this.form.submit();
        });

        // Handle vehicle type changes
        const typeInput = document.getElementById('vehicle_type');
        typeInput.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
@endsection 