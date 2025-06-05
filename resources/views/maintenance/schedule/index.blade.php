@extends('layouts.dashboard')

@section('header')
    Maintenance Schedule
@endsection

@section('content')
    <!-- Statistics -->
    <div class="mb-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Scheduled</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $stats['pending'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['overdue'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['completed'] }}</dd>
                </div>
            </div>
        </dl>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <form action="{{ route('maintenance.schedule.index') }}" method="GET" class="flex-1 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label for="vehicle" class="block text-sm font-medium text-gray-700">Vehicle</label>
                        <select id="vehicle" name="vehicle_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} - {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700">Date Range</label>
                        <select id="date_range" name="date_range"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </form>

                <div class="mt-4 md:mt-0 md:ml-4">
                    <a href="{{ route('maintenance.schedule.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Schedule Maintenance
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Schedule List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vehicle
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Maintenance Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Scheduled Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Assigned To
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($schedules as $schedule)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $schedule->vehicle->registration_number }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $schedule->vehicle->brand->name }} {{ $schedule->vehicle->model }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $schedule->maintenance_type }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $schedule->scheduled_date->format('M d, Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $schedule->scheduled_date->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($schedule->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($schedule->status === 'overdue') bg-red-100 text-red-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ ucfirst($schedule->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $schedule->assignedStaff ? $schedule->assignedStaff->name : 'Unassigned' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('maintenance.schedule.show', $schedule) }}"
                                                   class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                @if($schedule->status === 'pending')
                                                    <a href="#"
                                                       onclick="event.preventDefault(); document.getElementById('complete-form-{{ $schedule->id }}').submit();"
                                                       class="text-green-600 hover:text-green-900">Complete</a>
                                                    <form id="complete-form-{{ $schedule->id }}"
                                                          action="{{ route('maintenance.schedule.complete', $schedule) }}"
                                                          method="POST" class="hidden">
                                                        @csrf
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No maintenance schedules found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($schedules->hasPages())
                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection 