@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Welcome to your fleet management dashboard</p>
    </div>

    <!-- Main Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Vehicles -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Vehicles</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_vehicles'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.vehicles.index') }}" class="font-medium text-blue-600 hover:text-blue-500">View all vehicles</a>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_users'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.users.index') }}" class="font-medium text-green-600 hover:text-green-500">View all users</a>
                </div>
            </div>
        </div>

        <!-- Active Bookings -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Bookings</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['active_bookings'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.bookings.index', ['status' => 'in_progress']) }}" class="font-medium text-yellow-600 hover:text-yellow-500">View active bookings</a>
                </div>
            </div>
        </div>

        <!-- Pending Maintenance -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Maintenance</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['pending_maintenance'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.maintenance-schedules.index', ['status' => 'pending']) }}" class="font-medium text-red-600 hover:text-red-500">View pending maintenance</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Completed Maintenance -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Maintenance</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['completed_maintenance'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.maintenance-schedules.index', ['status' => 'completed']) }}" class="font-medium text-emerald-600 hover:text-emerald-500">View completed maintenance</a>
                </div>
            </div>
        </div>

        <!-- Total Bookings -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-pink-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_bookings'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.bookings.index') }}" class="font-medium text-pink-600 hover:text-pink-500">View all bookings</a>
                </div>
            </div>
        </div>

        <!-- Pending Feedback -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Feedback</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['pending_feedback'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.feedback.index', ['status' => 'pending']) }}" class="font-medium text-purple-600 hover:text-purple-500">View pending feedback</a>
                </div>
            </div>
        </div>

        <!-- Pending Vehicle Reports -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl transition-all duration-300 hover:shadow-md border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-500 rounded-lg p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Vehicle Reports</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['pending_vehicle_reports'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-2">
                <div class="text-sm">
                    <a href="{{ route('admin.vehicle-reports.index', ['status' => 'pending']) }}" class="font-medium text-orange-600 hover:text-orange-500">View pending reports</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activities</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Latest actions across the system</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <div class="flow-root">
                    <ul class="-mb-8">
                    @forelse($recent_activities as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                            @if($activity->type === 'booking') bg-yellow-500
                                            @elseif($activity->type === 'maintenance') bg-red-500
                                            @else bg-blue-500
                                            @endif">
                                            @if($activity->type === 'booking')
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif($activity->type === 'maintenance')
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-800">{{ $activity->description }}</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">No recent activities</li>
                    @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Pending Approvals</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Items requiring your attention</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <div class="flow-root">
                    <ul class="divide-y divide-gray-200">
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-700">Booking Requests</h4>
                                    <p class="text-xs text-gray-500">{{ $stats['pending_bookings'] }} pending</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View all
                            </a>
                        </li>
                        
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                    </svg>
                                </span>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-700">Service Requests</h4>
                                    <p class="text-xs text-gray-500">{{ $pendingServiceRequests->count() }} pending</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.service-requests.index', ['status' => 'pending']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View all
                            </a>
                        </li>
                        
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </span>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-gray-700">Vehicle Reports</h4>
                                    <p class="text-xs text-gray-500">{{ $pendingVehicleReports->count() }} pending</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.vehicle-reports.index', ['status' => 'pending']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View all
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Feedback</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Latest user feedback and reviews</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <div class="flow-root">
                    <ul class="divide-y divide-gray-200">
                        @forelse($recent_feedback as $feedback)
                        <li class="py-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($feedback->user && $feedback->user->image_data)
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $feedback->user->image_data }}" 
                                             alt="{{ $feedback->user->name }}'s profile photo">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ strtoupper(substr($feedback->user->name ?? 'User', 0, 2)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $feedback->user->name ?? 'Anonymous User' }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ Str::limit($feedback->comment, 100) }}
                                    </p>
                                    <div class="mt-1 flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $feedback->rating)
                                                    <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-xs text-gray-500">{{ $feedback->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-4 text-center text-gray-500">No feedback available</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection