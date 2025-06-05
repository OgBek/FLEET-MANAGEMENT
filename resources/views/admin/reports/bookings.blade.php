@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Bookings Report</h2>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200">
            <form action="{{ route('admin.reports.bookings') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Generate Report
                    </button>
                    @if(isset($bookings) && $bookings->count() > 0)
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.reports.export', ['type' => 'bookings']) }}?format=csv"
                               class="flex-1 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                CSV
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'bookings']) }}?format=pdf"
                               class="flex-1 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                PDF
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Statistics -->
        @if(isset($stats))
        <div class="p-4 border-b border-gray-200">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_bookings'] }}</dd>
                </div>
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Approved Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['approved_bookings'] }}</dd>
                </div>
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['pending_bookings'] }}</dd>
                </div>
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Bookings</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['completed_bookings'] }}</dd>
                </div>
            </dl>
        </div>
        @endif

        <!-- Bookings Table -->
        <div class="p-4">
            @if(isset($bookings) && $bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vehicle
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Department
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Details
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4">
                                        @if($booking->vehicle)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $booking->vehicle->brand->name ?? 'N/A' }} {{ $booking->vehicle->model ?? '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $booking->vehicle->registration_number ?? 'N/A' }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500 italic">
                                                Vehicle not found
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($booking->department)
                                            <div class="text-sm text-gray-900">
                                                {{ $booking->department->name }}
                                            </div>
                                        @endif
                                        @if($booking->requestedBy)
                                            <div class="text-sm text-gray-500">
                                                Requested by: {{ $booking->requestedBy->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $booking->start_time->format('M d, Y H:i') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            to {{ $booking->end_time->format('M d, Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $booking->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                               ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($booking->status === 'completed' ? 'bg-blue-100 text-blue-800' : 
                                                 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>Purpose: {{ $booking->purpose }}</div>
                                        <div>Destination: {{ $booking->destination }}</div>
                                        <div>Passengers: {{ $booking->number_of_passengers }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($bookings->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $bookings->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">No bookings found for the selected criteria.</p>
                </div>
            @endif
        </div>
    </div>
@endsection 