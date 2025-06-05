@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Maintenance Report</h2>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="p-4 border-b border-gray-200">
        <form action="{{ route('admin.reports.maintenance') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                <select name="maintenance_type" id="maintenance_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($maintenanceTypes as $type)
                        <option value="{{ $type }}" {{ request('maintenance_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Generate Report
                </button>
                @if(isset($paginator) && $paginator->count() > 0)
                    <a href="{{ route('admin.reports.export', ['type' => 'maintenance']) }}"
                       class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Export to CSV
                    </a>
                    <a href="{{ route('admin.reports.export', ['type' => 'maintenance', 'format' => 'pdf']) }}"
                       class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Export to PDF
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Statistics -->
    @if(isset($stats))
    <div class="p-4 border-b border-gray-200">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Records</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_records'] }}</dd>
            </div>
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['completed_records'] }}</dd>
            </div>
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['pending_records'] }}</dd>
            </div>
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Cost</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['total_cost'], 2) }} BIRR</dd>
            </div>
        </dl>
    </div>
    @endif

    <!-- Records Table -->
    <div class="p-4">
        @if(isset($paginator) && $paginator->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($paginator as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($item['vehicle'])
                                            {{ $item['vehicle']->brand->name ?? 'Unknown' }} {{ $item['vehicle']->model ?? 'Unknown' }}
                                        @else
                                            Unknown Vehicle
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($item['vehicle'])
                                            {{ $item['vehicle']->registration_number ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $item['service_type'] ?? 'Unknown')) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($item['type'] == 'record')
                                            Service Date: {{ $item['date']->format('M d, Y') }}
                                        @elseif($item['type'] == 'schedule')
                                            Scheduled Date: {{ $item['date']->format('M d, Y') }}
                                        @else
                                            Task Date: {{ $item['date']->format('M d, Y') }}
                                        @endif
                                    </div>
                                    @if($item['next_service_date'] && $item['type'] != 'task')
                                        <div class="text-sm text-gray-500">
                                            Next Service: {{ $item['next_service_date']->format('M d, Y') }}
                                        </div>
                                    @elseif($item['next_service_date'] && $item['type'] == 'task')
                                        <div class="text-sm text-gray-500">
                                            Completed: {{ $item['next_service_date']->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        @if($item['staff'])
                                            {{ $item['staff']->name ?? 'Unassigned' }}
                                        @else
                                            Unassigned
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($item['staff'])
                                            {{ $item['staff']->specialization ?? '' }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $item['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($item['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($item['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                             'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($item['status'] ?? 'Unknown') }}
                                    </span>
                                    <div class="mt-1 text-xs text-gray-500">
                                        @if($item['type'] == 'record')
                                            Record
                                        @elseif($item['type'] == 'schedule')
                                            Schedule
                                        @else
                                            Task
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ number_format($item['cost'] ?? 0, 2) }} BIRR
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($paginator->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $paginator->appends([
                        'start_date' => request('start_date'),
                        'end_date' => request('end_date'),
                        'maintenance_type' => request('maintenance_type')
                    ])->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <p class="text-gray-500">No maintenance records found for the selected criteria.</p>
            </div>
        @endif
    </div>
</div>
@endsection 