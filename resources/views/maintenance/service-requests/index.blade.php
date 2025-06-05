@extends('layouts.dashboard')

@section('header')
    Service Requests
@endsection

@section('content')
    <!-- Statistics -->
    <div class="mb-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">New Requests</dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $stats['pending'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['in_progress'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Today</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['completed_today'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Urgent Requests</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['urgent'] }}</dd>
                </div>
            </div>
        </dl>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('maintenance.service-requests.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div>
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->registration_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date Range</label>
                    <input type="date" id="date" name="date" value="{{ request('date') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Requests List -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Service Requests</h2>
            </div>

            @if($requests->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No service requests found</h3>
                    <p class="mt-1 text-sm text-gray-500">You don't have any service requests assigned to you yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($requests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $request->vehicle->registration_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $request->vehicle->formatted_brand }} {{ $request->vehicle->model }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $request->issue_title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($request->issue_description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->requestedBy->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->requestedBy->department->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($request->status === 'completed') bg-green-100 text-green-800
                                            @elseif($request->status === 'in_progress') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->scheduled_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('maintenance.service-requests.show', $request) }}" 
                                           class="text-blue-600 hover:text-blue-900">View Details</a>
                                        
                                        @if($request->status === 'approved')
                                            <form action="{{ route('maintenance.service-requests.start', $request) }}" method="POST" class="inline ml-3">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Start Work</button>
                                            </form>
                                        @elseif($request->status === 'in_progress')
                                            <button onclick="openCompleteModal('{{ $request->id }}')" 
                                                    class="text-green-600 hover:text-green-900 ml-3">Complete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Complete Service Request Modal -->
    <div id="complete-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" aria-hidden="true">
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                        <button type="button" onclick="closeCompleteModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Complete Service Request</h3>
                            <form id="complete-form" action="" method="POST" class="mt-4">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes</label>
                                        <textarea id="resolution_notes" name="resolution_notes" rows="3" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>
                                    <div>
                                        <label for="parts_used" class="block text-sm font-medium text-gray-700">Parts Used</label>
                                        <input type="text" id="parts_used" name="parts_used" 
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours</label>
                                        <input type="number" step="1" id="labor_hours" name="labor_hours" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost (birr)</label>
                                        <input type="number" step="100" id="total_cost" name="total_cost" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-sm text-gray-500">Include all parts and labor costs</p>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                        Complete Service Request
                                    </button>
                                    <button type="button" onclick="closeCompleteModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openCompleteModal(requestId) {
        const modal = document.getElementById('complete-modal');
        const form = document.getElementById('complete-form');
        form.action = `/fleet/maintenance/service-requests/${requestId}/complete`;
        modal.classList.remove('hidden');
    }

    function closeCompleteModal() {
        const modal = document.getElementById('complete-modal');
        const form = document.getElementById('complete-form');
        form.reset();
        modal.classList.add('hidden');
    }
</script>
@endpush