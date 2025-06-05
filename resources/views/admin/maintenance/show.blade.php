@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <!-- Header -->
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Maintenance Record Details</h3>
                    <div class="flex space-x-3">
                        @if($maintenance->status !== 'completed')
                            <a href="{{ route('admin.maintenance.edit', $maintenance) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Edit Record
                            </a>
                        @endif
                        <a href="{{ route('admin.maintenance.index') }}" 
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
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $maintenance->vehicle->registration_number }} - {{ $maintenance->vehicle->model }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Maintenance Staff</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $maintenance->maintenanceStaff->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ str_replace('_', ' ', ucfirst($maintenance->service_type)) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $maintenance->priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                           ($maintenance->priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                           ($maintenance->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-green-100 text-green-800')) }}">
                                        {{ ucfirst($maintenance->priority) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-800' :
                                           ($maintenance->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-gray-100 text-gray-800') }}">
                                        {{ str_replace('_', ' ', ucfirst($maintenance->status)) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Schedule Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Schedule Information</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $maintenance->scheduled_date->format('M d, Y H:i') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estimated Completion</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $maintenance->estimated_completion_date->format('M d, Y H:i') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Service Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $maintenance->service_date ? $maintenance->service_date->format('M d, Y') : 'Not started' }}
                                </dd>
                            </div>
                            @if($maintenance->next_service_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Next Service Due</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $maintenance->next_service_date->format('M d, Y') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Cost Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Cost Information</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estimated Cost</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($maintenance->estimated_cost, 2) }} birr</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Actual Cost</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($maintenance->cost, 2) }} birr</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Labor Hours</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $maintenance->labor_hours }} hours</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Odometer Reading</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($maintenance->odometer_reading) }} km</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Details -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Maintenance Details</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $maintenance->description }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Parts Required</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $maintenance->parts_required ?: 'None specified' }}</dd>
                            </div>
                            @if($maintenance->parts_replaced)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Parts Replaced</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($maintenance->parts_replaced as $part)
                                                <li>{{ $part }}</li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            @endif
                            @if($maintenance->notes)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $maintenance->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Related Records -->
                    @if($relatedRecords->isNotEmpty())
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Related Maintenance Records</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($relatedRecords as $record)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $record->scheduled_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ str_replace('_', ' ', ucfirst($record->service_type)) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' :
                                                           ($record->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                                            'bg-gray-100 text-gray-800') }}">
                                                        {{ str_replace('_', ' ', ucfirst($record->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('admin.maintenance.show', $record) }}" 
                                                       class="text-blue-600 hover:text-blue-900">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 