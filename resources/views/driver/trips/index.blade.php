@extends('layouts.dashboard')

@section('header')
    My Trips
@endsection

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">My Trips</h2>
                <div class="flex items-center space-x-4">
                    <!-- Status Filter -->
                    <select id="status-filter" onchange="updateFilter(this.value)" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Trips</option>
                        <option value="in_progress" {{ ($status ?? '') === 'in_progress' ? 'selected' : '' }}>Active Trips</option>
                        <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Upcoming Trips</option>
                        <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="p-4 border-b border-gray-200">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Trips</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $statistics['total_trips'] ?? 0 }}</dd>
                    </div>
                </div>

                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Trips</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $statistics['active_trips'] ?? 0 }}</dd>
                    </div>
                </div>

                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Trips</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $statistics['upcoming_trips'] ?? 0 }}</dd>
                    </div>
                </div>
            </dl>
        </div>

        <!-- Trips List -->
        <div class="p-4">
            @if(count($trips) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($trips as $trip)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($trip->vehicle)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $trip->vehicle->brand?->name ?? 'N/A' }} {{ $trip->vehicle->model ?? '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $trip->vehicle->registration_number ?? 'N/A' }}
                                            </div>
                                        @else
                                            <div class="text-sm font-medium text-red-500">
                                                Vehicle not found
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($trip->department)
                                            <div class="text-sm text-gray-900">{{ $trip->department->name }}</div>
                                        @else
                                            <div class="text-sm text-gray-500 italic">No department</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $trip->start_time->format('M d, Y H:i') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            to {{ $trip->end_time->format('M d, Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($trip->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                               ($trip->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                                'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst($trip->status === 'in_progress' ? 'active' : ($trip->status === 'approved' ? 'upcoming' : $trip->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('driver.trips.show', $trip) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($trips->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $trips->links() }}
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-4">No trips found.</p>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function updateFilter(status) {
            const url = new URL(window.location.href);
            url.searchParams.set('status', status);
            window.location.href = url.toString();
        }
    </script>
    @endpush
@endsection