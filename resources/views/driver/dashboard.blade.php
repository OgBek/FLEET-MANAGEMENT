@extends('layouts.dashboard')

@section('header')
    Driver Dashboard
@endsection

@section('navigation')
    <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
        Dashboard
    </a>
    <a href="{{ route('driver.trips.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        My Trips
    </a>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Statistics Grid -->
        <div class="mb-6">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative bg-white pt-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <dt>
                        <div class="absolute bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <p class="ml-16 text-sm font-medium text-gray-500 truncate">Active Trips</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_trips'] }}</p>
                    </dd>
                </div>

                <div class="relative bg-white pt-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <dt>
                        <div class="absolute bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="ml-16 text-sm font-medium text-gray-500 truncate">Completed Trips</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['completed_trips'] }}</p>
                    </dd>
                </div>

                <div class="relative bg-white pt-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <dt>
                        <div class="absolute bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="ml-16 text-sm font-medium text-gray-500 truncate">Upcoming Trips</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['upcoming_trips'] }}</p>
                    </dd>
                </div>

                <div class="relative bg-white pt-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <dt>
                        <div class="absolute bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <p class="ml-16 text-sm font-medium text-gray-500 truncate">Average Rating</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['average_rating'] }}/5</p>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Active and Upcoming Trips -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Active Trips -->
                @if($activeTrips->count() > 0)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Active Trips</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($activeTrips as $trip)
                                <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="text-base font-medium text-gray-900">{{ $trip->vehicle->registration_number }}</h4>
                                                    <p class="text-sm text-gray-500">{{ $trip->department->name }}</p>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-500">
                                                <p>Requested by: {{ $trip->requestedBy->name }}</p>
                                                <p class="mt-1">Until: {{ $trip->end_time->format('M d, Y H:i') }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('driver.trips.show', $trip) }}" 
                                           class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Upcoming Trips -->
                @if($upcomingTrips->count() > 0)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Upcoming Trips</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($upcomingTrips as $trip)
                                <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="text-base font-medium text-gray-900">{{ $trip->vehicle->registration_number }}</h4>
                                                    <p class="text-sm text-gray-500">{{ $trip->department->name }}</p>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-500">
                                                <p>Requested by: {{ $trip->requestedBy->name }}</p>
                                                <p class="mt-1">Start: {{ $trip->start_time->format('M d, Y H:i') }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('driver.trips.show', $trip) }}" 
                                           class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Recent Activities -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Activities</h3>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($recentActivities as $activity)
                                <li class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="relative">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @switch($activity['type'])
                                                    @case('trip_completed')
                                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                        @break
                                                    @case('trip_started')
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </div>
                                                        @break
                                                    @case('trip_assigned')
                                                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                            </svg>
                                                        </div>
                                                        @break
                                                    @case('feedback_received')
                                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                            </svg>
                                                        </div>
                                                        @break
                                                @endswitch
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $activity['message'] }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $activity['timestamp'] ? $activity['timestamp']->diffForHumans() : 'Recently' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-6">
                                    <p class="text-sm text-gray-500 text-center">No recent activities</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column: Vehicle Reports and Feedback -->
            <div class="space-y-6">
                <!-- Vehicle Reports -->
                @if($vehicleReports->count() > 0)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Vehicle Reports</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($vehicleReports as $report)
                                <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-8 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex justify-between">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $report['vehicle']->registration_number }} - {{ $report['vehicle']->model }}
                                                </p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report['vehicle']->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($report['vehicle']->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-1 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                                <p class="text-gray-500">
                                                    <span class="font-medium text-gray-700">Total Trips:</span> {{ $report['total_trips'] }}
                                                </p>
                                                <p class="text-gray-500">
                                                    <span class="font-medium text-gray-700">Total Hours:</span> {{ $report['total_hours'] }}
                                                </p>
                                                <p class="text-gray-500">
                                                    <span class="font-medium text-gray-700">Avg Rating:</span> {{ $report['avg_rating'] }}/5
                                                </p>
                                                <p class="text-gray-500">
                                                    <span class="font-medium text-gray-700">Last Trip:</span> 
                                                    {{ $report['latest_trip'] ? $report['latest_trip']->created_at->diffForHumans() : 'N/A' }}
                                                </p>
                                            </div>
                                            @if($report['vehicle']->next_maintenance_date)
                                                <p class="mt-2 text-xs {{ Carbon\Carbon::parse($report['vehicle']->next_maintenance_date)->isPast() ? 'text-red-500' : 'text-gray-400' }}">
                                                    Next maintenance: {{ Carbon\Carbon::parse($report['vehicle']->next_maintenance_date)->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Recent Feedback -->
                @if($recentFeedback->count() > 0)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Feedback</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($recentFeedback as $feedback)
                                <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <span class="text-lg font-medium text-purple-600">{{ $feedback->rating }}</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $feedback->user->name }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ Str::limit($feedback->content, 100) }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400">
                                                {{ $feedback->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection