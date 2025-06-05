@extends('layouts.dashboard')

@section('header')
    Maintenance Schedule Details
@endsection

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Status Banner -->
        <div class="mb-6">
            <div class="rounded-md p-4 
                @if($schedule->status === 'completed') bg-green-50 
                @elseif($schedule->status === 'overdue') bg-red-50 
                @else bg-blue-50 
                @endif">
                <div class="flex">
                    <div class="flex-shrink-0">
                        @if($schedule->status === 'completed')
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @elseif($schedule->status === 'overdue')
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium 
                            @if($schedule->status === 'completed') text-green-800 
                            @elseif($schedule->status === 'overdue') text-red-800 
                            @else text-blue-800 
                            @endif">
                            Status: {{ ucfirst($schedule->status) }}
                        </h3>
                        <div class="mt-2 text-sm 
                            @if($schedule->status === 'completed') text-green-700 
                            @elseif($schedule->status === 'overdue') text-red-700 
                            @else text-blue-700 
                            @endif">
                            @if($schedule->status === 'completed')
                                Maintenance completed successfully
                            @elseif($schedule->status === 'overdue')
                                This maintenance is overdue and requires immediate attention
                            @else
                                Scheduled for {{ $schedule->scheduled_date->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Schedule Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Schedule Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Maintenance Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->maintenance_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->scheduled_date->format('M d, Y') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->description }}</dd>
                        </div>
                        @if($schedule->mileage_interval)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Mileage Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($schedule->mileage_interval) }} km</dd>
                        </div>
                        @endif
                        @if($schedule->time_interval_days)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Time Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->time_interval_days }} days</dd>
                        </div>
                        @endif
                        @if($schedule->assignedStaff)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->assignedStaff->name }}</dd>
                        </div>
                        @endif
                        @if($schedule->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Vehicle Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->vehicle->registration_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Model</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->vehicle->brand->name }} {{ $schedule->vehicle->model }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $schedule->vehicle->type->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($schedule->vehicle->current_mileage) }} km</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Maintenance</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $schedule->vehicle->last_maintenance_date ? $schedule->vehicle->last_maintenance_date->format('M d, Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($schedule->vehicle->status) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Maintenance History -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Maintenance History</h3>
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Service Date
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Service Type
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cost
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Odometer
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Staff
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($maintenanceHistory as $record)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $record->service_date->format('M d, Y') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $record->service_type }}</div>
                                                    @if($record->parts_replaced)
                                                        <div class="text-xs text-gray-500">
                                                            Parts: {{ is_array($record->parts_replaced) ? implode(', ', $record->parts_replaced) : $record->parts_replaced }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">${{ number_format($record->cost, 2) }}</div>
                                                    @if($record->labor_hours)
                                                        <div class="text-xs text-gray-500">
                                                            {{ $record->labor_hours }} hours
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($record->odometer_reading) }} km
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $record->maintenanceStaff->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($record->status === 'completed') bg-green-100 text-green-800
                                                        @elseif($record->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ ucfirst($record->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    No maintenance history available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($schedule->status === 'pending')
        <!-- Completion Form -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Complete Maintenance</h3>
                <form action="{{ route('maintenance.schedule.complete', $schedule) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="service_type" class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select id="service_type" name="service_type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="{{ $schedule->maintenance_type }}">{{ $schedule->maintenance_type }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" id="cost" name="cost" required
                                       class="block w-full pl-7 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="odometer_reading" class="block text-sm font-medium text-gray-700">Odometer Reading (km)</label>
                            <input type="number" min="{{ $schedule->vehicle->current_mileage }}" id="odometer_reading" name="odometer_reading" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours</label>
                            <input type="number" step="0.5" min="0" id="labor_hours" name="labor_hours" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="parts_replaced" class="block text-sm font-medium text-gray-700">Parts Replaced</label>
                            <input type="text" id="parts_replaced" name="parts_replaced"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Comma separated list of parts">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('maintenance.schedule.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Complete Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end">
            <a href="{{ route('maintenance.schedule.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back to Schedule
            </a>
        </div>
        @endif
    </div>
@endsection 