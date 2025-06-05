@extends('layouts.dashboard')

@section('header')
    Maintenance Schedules
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Monthly Maintenance Schedule</h2>
                <div class="flex space-x-4">
                    <a href="{{ route('maintenance.schedules.index', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Previous Month
                    </a>
                    <span class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <a href="{{ route('maintenance.schedules.index', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Next Month
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="bg-gray-50 p-2 text-center text-sm font-medium text-gray-500">
                        {{ $dayName }}
                    </div>
                @endforeach

                @php
                    $startOfMonth = $currentMonth->copy()->startOfMonth();
                    $endOfMonth = $currentMonth->copy()->endOfMonth();
                    $date = $startOfMonth->copy()->startOfWeek();
                    $endDate = $endOfMonth->copy()->endOfWeek();
                @endphp

                @while($date <= $endDate)
                    @php
                        $dateStr = $date->format('Y-m-d');
                        $isToday = $date->isToday();
                        $isCurrentMonth = $date->month === $currentMonth->month;
                    @endphp
                    
                    <div class="min-h-[120px] bg-white p-2 {{ $isCurrentMonth ? '' : 'bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm {{ $isToday ? 'font-bold text-blue-600' : ($isCurrentMonth ? 'text-gray-900' : 'text-gray-400') }}">
                                {{ $date->format('j') }}
                            </span>
                        </div>

                        @if(isset($groupedSchedules[$dateStr]))
                            <div class="space-y-1">
                                @foreach($groupedSchedules[$dateStr] as $task)
                                    <a href="{{ $task['route'] }}" 
                                       class="block p-1 text-xs rounded
                                       @if($task['type'] === 'schedule')
                                           {{ $task['status'] === 'pending' ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 
                                              ($task['status'] === 'in_progress' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 
                                              ($task['status'] === 'completed' ? 'bg-green-50 text-green-700 hover:bg-green-100' : 
                                              'bg-red-50 text-red-700 hover:bg-red-100')) }}
                                       @elseif($task['type'] === 'service_request')
                                           {{ $task['status'] === 'approved' ? 'bg-purple-50 text-purple-700 hover:bg-purple-100' : 
                                              ($task['status'] === 'in_progress' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 
                                              'bg-green-50 text-green-700 hover:bg-green-100') }}
                                           {{ $task['priority'] === 'urgent' ? 'border-2 border-red-300' : '' }}
                                       @elseif($task['type'] === 'vehicle_report')
                                           @if($task['is_overdue'])
                                               bg-red-50 text-red-700 hover:bg-red-100 border-2 border-red-300
                                           @else
                                               {{ $task['status'] === 'in_progress' ? 'bg-orange-50 text-orange-700 hover:bg-orange-100' : 
                                                  ($task['status'] === 'resolved' ? 'bg-green-50 text-green-700 hover:bg-green-100' : 
                                                  'bg-gray-50 text-gray-700 hover:bg-gray-100') }}
                                               {{ $task['severity'] === 'high' ? 'border-l-4 border-red-500' : '' }}
                                           @endif
                                       @elseif($task['type'] === 'task')
                                           {{ $task['status'] === 'pending' ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 
                                              ($task['status'] === 'in_progress' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 
                                              'bg-green-50 text-green-700 hover:bg-green-100') }}
                                           {{ $task['priority'] === 'urgent' ? 'border-2 border-red-300' : 
                                              ($task['priority'] === 'high' ? 'border-l-4 border-orange-500' : '') }}
                                       @endif">
                                        @if($task['type'] === 'schedule')
                                            <span class="font-medium">Schedule:</span> {{ Str::limit($task['title'], 20) }} - {{ $task['vehicle']->registration_number }}
                                        @elseif($task['type'] === 'service_request')
                                            <span class="font-medium">Service:</span> {{ Str::limit($task['title'], 20) }} - {{ $task['vehicle']->registration_number }}
                                            @if($task['priority'] === 'urgent')
                                                <span class="inline-block px-1 text-xs bg-red-100 text-red-800 rounded">Urgent</span>
                                            @endif
                                        @elseif($task['type'] === 'vehicle_report')
                                            <div class="flex flex-col">
                                                <div class="flex items-center">
                                                    <span class="font-medium">Report:</span> {{ Str::limit($task['title'], 20) }}
                                                    @if($task['vehicle'])
                                                        <span class="ml-1 text-xs text-gray-500">({{ $task['vehicle']->registration_number }})</span>
                                                    @endif
                                                </div>
                                                @if($task['is_overdue'])
                                                    <span class="inline-block mt-1 px-1 text-xs bg-red-100 text-red-800 rounded">Overdue</span>
                                                @elseif($task['severity'] === 'high')
                                                    <span class="inline-block mt-1 px-1 text-xs bg-red-100 text-red-800 rounded">High Severity</span>
                                                @endif
                                                @if($task['date'])
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        @if($task['is_overdue'])
                                                            Was due: {{ $task['date']->format('M j') }}
                                                        @else
                                                            Due: {{ $task['date']->format('M j') }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($task['type'] === 'task')
                                            <div class="flex flex-col">
                                                <div class="flex items-center">
                                                    <span class="font-medium {{ $task['status'] === 'completed' ? 'line-through' : '' }}">
                                                        Task: {{ Str::limit($task['title'], 20) }}
                                                    </span>
                                                    @if($task['vehicle'])
                                                        <span class="ml-1 text-xs text-gray-500">({{ $task['vehicle']->registration_number }})</span>
                                                    @endif
                                                </div>
                                                @if($task['status'] === 'completed')
                                                    <span class="inline-block mt-1 px-1 text-xs bg-green-100 text-green-800 rounded">
                                                        Completed {{ $task['model']->completed_at ? $task['model']->completed_at->diffForHumans() : '' }}
                                                    </span>
                                                @else
                                                    @if($task['priority'] === 'urgent')
                                                        <span class="inline-block mt-1 px-1 text-xs bg-red-100 text-red-800 rounded">Urgent</span>
                                                    @elseif($task['priority'] === 'high')
                                                        <span class="inline-block mt-1 px-1 text-xs bg-orange-100 text-orange-800 rounded">High Priority</span>
                                                    @endif
                                                    @if($task['status'] === 'in_progress')
                                                        <span class="inline-block mt-1 px-1 text-xs bg-yellow-100 text-yellow-800 rounded">In Progress</span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @php
                        $date->addDay();
                    @endphp
                @endwhile
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Legend</h3>
            <div class="flex space-x-6">
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-blue-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Pending</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-yellow-100 mr-2"></span>
                    <span class="text-sm text-gray-600">In Progress</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-green-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Completed</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-red-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Overdue</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-purple-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Approved Service Request</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-orange-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Vehicle Report</span>
                </div>
                <div class="flex items-center">
                    <div class="h-4 w-4 border-l-4 border-red-500 bg-white mr-2"></div>
                    <span class="text-sm text-gray-600">High Severity Report</span>
                </div>
                <div class="flex items-center">
                    <div class="h-4 w-4 border-2 border-red-300 rounded-full bg-white mr-2"></div>
                    <span class="text-sm text-gray-600">Urgent Priority</span>
                </div>
                <div class="flex items-center">
                    <div class="h-4 w-4 border-2 border-red-300 bg-red-50 mr-2"></div>
                    <span class="text-sm text-gray-600">Overdue Report</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-indigo-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Maintenance Task</span>
                </div>
                <div class="flex items-center">
                    <div class="h-4 w-4 border-l-4 border-orange-500 bg-white mr-2"></div>
                    <span class="text-sm text-gray-600">High Priority Task</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('maintenance.service-requests.create') }}" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center">
                    Schedule Service
                </a>
                <a href="{{ route('maintenance.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center">
                    Return to Dashboard
                </a>
                <a href="{{ route('maintenance.tasks.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center">
                    View All Tasks
                </a>
            </div>
        </div>
    </div>
@endsection
