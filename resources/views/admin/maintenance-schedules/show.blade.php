@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Maintenance Schedule Details</h1>
            <div class="flex space-x-3">
                @if($maintenanceSchedule->status !== 'completed')
                    <a href="{{ route('admin.maintenance-schedules.edit', $maintenanceSchedule) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Schedule
                    </a>
                @endif
                <a href="{{ route('admin.maintenance-schedules.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information Card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Schedule Information</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Basic details about this maintenance schedule</p>
            </div>
            <div class="px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->vehicle->model ?? 'N/A' }} - {{ $maintenanceSchedule->vehicle->registration_number ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Maintenance Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ ucwords(str_replace('_', ' ', $maintenanceSchedule->maintenance_type)) }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->description }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->scheduled_date->format('F d, Y') }}
                            <span class="text-gray-500">({{ $maintenanceSchedule->scheduled_date->diffForHumans() }})</span>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 sm:mt-0 sm:col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $maintenanceSchedule->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $maintenanceSchedule->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $maintenanceSchedule->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($maintenanceSchedule->status) }}
                            </span>
                        </dd>
                    </div>
                    @if($maintenanceSchedule->mileage_interval)
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Mileage Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ number_format($maintenanceSchedule->mileage_interval) }} km
                            </dd>
                        </div>
                    @endif
                    @if($maintenanceSchedule->time_interval_days)
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Time Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $maintenanceSchedule->time_interval_days }} days
                            </dd>
                        </div>
                    @endif
                    @if($maintenanceSchedule->notes)
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $maintenanceSchedule->notes }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
        
        <!-- Service Details Card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Service Details</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Information about the service performed</p>
            </div>
            <div class="px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Service Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->total_cost ? 'birr '.number_format($maintenanceSchedule->total_cost, 2) : 'Not specified' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Labor Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->labor_hours ?? 'Not specified' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->assignedStaff ? $maintenanceSchedule->assignedStaff->name : 'Not Assigned' }}
                        </dd>
                    </div>
                    @if($maintenanceSchedule->completed_at)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Completion Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $maintenanceSchedule->completed_at->format('F d, Y H:i') }}
                            <span class="text-gray-500">({{ $maintenanceSchedule->completed_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                    @endif
                    @if($maintenanceSchedule->resolution_notes)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Resolution Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                            {{ $maintenanceSchedule->resolution_notes }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Technician Details Card -->
    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Technician Details</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Information about the assigned technician</p>
        </div>
        <div class="px-4 py-5 sm:p-0">
            @if($maintenanceSchedule->assignedStaff)
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $maintenanceSchedule->assignedStaff->name }}</dd>
                    </div>
                    
                    @if($maintenanceSchedule->assignedStaff->email)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $maintenanceSchedule->assignedStaff->email }}</dd>
                    </div>
                    @endif
                    
                    @if($maintenanceSchedule->assignedStaff->phone)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $maintenanceSchedule->assignedStaff->phone }}</dd>
                    </div>
                    @endif
                    
                    @if($maintenanceSchedule->assignedStaff->position)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Position</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $maintenanceSchedule->assignedStaff->position }}</dd>
                    </div>
                    @endif
                    
                    @if($maintenanceSchedule->assignedStaff->department)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $maintenanceSchedule->assignedStaff->department->name}}</dd>
                    </div>
                    @endif
                    
                    @if($maintenanceSchedule->technician_notes)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Work Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">{{ $maintenanceSchedule->technician_notes }}</dd>
                    </div>
                    @endif
                </dl>
            @else
                <div class="px-4 py-5 sm:px-6">
                    <p class="text-sm text-gray-500">No technician assigned to this maintenance task</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Vehicle Maintenance History Card -->
    @if($maintenanceHistory->count() > 0)
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Maintenance History</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Previous maintenance records for this vehicle</p>
            </div>
            <div class="bg-white overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    @foreach($maintenanceHistory as $record)
                        <li class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ucwords(str_replace('_', ' ', $record->maintenance_type)) }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $record->description }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900">{{ $record->completed_at->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $record->completed_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Related Schedules Card -->
    @if($relatedSchedules->count() > 0)
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Other Pending Schedules</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Other maintenance tasks for this vehicle</p>
            </div>
            <div class="bg-white overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    @foreach($relatedSchedules as $schedule)
                        <li class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ucwords(str_replace('_', ' ', $schedule->maintenance_type)) }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $schedule->description }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900">{{ $schedule->scheduled_date->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $schedule->scheduled_date->diffForHumans() }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
