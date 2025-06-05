@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Maintenance Schedule</h2>
                <a href="{{ route('admin.maintenance-schedules.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Schedule
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200">
            <form action="{{ route('admin.maintenance.schedule') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <select name="vehicle_id" id="vehicle_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->registration_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="mechanic_id" class="block text-sm font-medium text-gray-700">Maintenance Staff</label>
                    <select name="mechanic_id" id="mechanic_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Staff</option>
                        @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ request('mechanic_id') == $mechanic->id ? 'selected' : '' }}>
                                {{ $mechanic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" id="date_from" 
                           value="{{ request('date_from') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" id="date_to" 
                           value="{{ request('date_to') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Filter Results
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                    {{ $schedule->vehicle->brand?->name }} {{ $schedule->vehicle->model }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $schedule->assignedStaff ? $schedule->assignedStaff->name : 'Not assigned' }}</div>
                                <div class="text-sm text-gray-500">{{ $schedule->assignedStaff && $schedule->assignedStaff->phone ? $schedule->assignedStaff->phone : '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $schedule->description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($schedule->status === 'completed') bg-green-100 text-green-800
                                    @elseif($schedule->status === 'in_progress') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ str_replace('_', ' ', ucfirst($schedule->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.maintenance-schedules.edit', $schedule) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="{{ route('admin.maintenance-schedules.show', $schedule) }}" 
                                   class="text-green-600 hover:text-green-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No maintenance schedules found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($schedules->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
@endsection