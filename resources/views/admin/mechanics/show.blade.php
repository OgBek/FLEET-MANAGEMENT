@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <!-- Header -->
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Mechanic Details</h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.mechanics.edit', $mechanic) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Edit Mechanic
                        </a>
                        <a href="{{ route('admin.mechanics.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $mechanic->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $mechanic->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $mechanic->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $mechanic->specialization }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $mechanic->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($mechanic->status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Pending Maintenance Tasks -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Pending Maintenance Tasks</h4>
                        @if($mechanic->maintenanceSchedules->isNotEmpty())
                            <ul class="divide-y divide-gray-200">
                                @foreach($mechanic->maintenanceSchedules as $schedule)
                                    <li class="py-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $schedule->vehicle->brand->name }} {{ $schedule->vehicle->model }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    Scheduled: {{ $schedule->scheduled_date->format('M d, Y H:i') }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    Type: {{ $schedule->maintenance_type }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">No pending maintenance tasks.</p>
                        @endif
                    </div>

                    <!-- Recent Maintenance Records -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Maintenance Records</h4>
                        @if($mechanic->maintenanceRecords->isNotEmpty())
                            <ul class="divide-y divide-gray-200">
                                @foreach($mechanic->maintenanceRecords as $record)
                                    <li class="py-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $record->vehicle->brand->name }} {{ $record->vehicle->model }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    Date: {{ $record->service_date ? $record->service_date->format('M d, Y H:i') : 'Not completed' }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    Type: {{ $record->service_type }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </div>
                                        @if($record->notes)
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">Notes: {{ $record->notes }}</p>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">No maintenance records found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 