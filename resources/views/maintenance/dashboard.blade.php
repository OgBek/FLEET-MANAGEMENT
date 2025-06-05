@extends('layouts.dashboard')

@section('header')
    Maintenance Dashboard
@endsection

@section('navigation')
    <a href="{{ route('maintenance.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-blue-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-blue-700 transition duration-150 ease-in-out">
        Dashboard
    </a>
    <a href="{{ route('maintenance.tasks.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Maintenance Tasks
    </a>
    <a href="{{ route('maintenance.service-requests.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Service Requests
    </a>
    <a href="{{ route('maintenance.schedules.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        Schedules
    </a>
@endsection

@section('content')
    <!-- Quick Actions -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('maintenance.tasks.index') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-lg font-medium text-gray-900">My Tasks</h3>
                            <p class="text-sm text-gray-500">View and manage assigned maintenance tasks</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('maintenance.service-requests.create') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-lg font-medium text-gray-900">Schedule Service</h3>
                            <p class="text-sm text-gray-500">Schedule a vehicle for maintenance check</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('maintenance.service-requests.index') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-lg font-medium text-gray-900">Service Requests</h3>
                            <p class="text-sm text-gray-500">View all vehicle service requests</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Assigned Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">My Assigned Tasks</h3>
            </div>
            <div class="p-5">
                <div class="flow-root">
                    <ul class="-my-5 divide-y divide-gray-200">
                        @forelse($tasks as $task)
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            @if(is_object($task->vehicle))
                                                {{ $task->vehicle->registration_number }} - {{ $task->maintenance_type }}
                                            @else
                                                Vehicle Info Unavailable - {{ $task->maintenance_type ?? 'Unknown Task' }}
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            @if(isset($task->scheduled_date))
                                                Scheduled: {{ $task->scheduled_date->format('M d, Y') }}
                                            @else
                                                Scheduled: Date unavailable
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            @if(isset($task->description))
                                                Description: {{ Str::limit($task->description, 50) }}
                                            @else
                                                Description: Not available
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        @if(isset($task->status))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($task->status === 'completed') bg-green-100 text-green-800
                                                @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($task->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Unknown
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4 text-center text-gray-500">No assigned tasks</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Service Requests -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Service Requests</h3>
            </div>
            <div class="p-5">
                <div class="flow-root">
                    <ul class="-my-5 divide-y divide-gray-200">
                        @forelse($serviceRequests as $request)
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            @if(is_object($request) && is_object($request->vehicle ?? null))
                                                {{ $request->vehicle->registration_number }} - {{ $request->issue_title }}
                                            @elseif(is_object($request))
                                                Vehicle Info Unavailable - {{ $request->issue_title }}
                                            @else
                                                Request Details Unavailable
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            @if(is_object($request) && isset($request->scheduled_date))
                                                Scheduled: {{ $request->scheduled_date->format('M d, Y') }}
                                            @else
                                                Scheduled: Date unavailable
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            @if(is_object($request) && isset($request->priority))
                                                Priority: <span class="font-medium">{{ ucfirst($request->priority) }}</span>
                                            @else
                                                Priority: Not available
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        @if(is_object($request) && isset($request->status))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($request->status === 'completed') bg-green-100 text-green-800
                                                @elseif($request->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Unknown
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="text-center text-gray-500">No service requests</div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Statistics -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Maintenance Statistics</h3>
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                <div class="bg-gray-50 px-4 py-5 rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Tasks Completed</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['completed_tasks'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Tasks In Progress</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['in_progress_tasks'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Tasks</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['pending_tasks'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Urgent Requests</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['urgent_requests'] }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection 