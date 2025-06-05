@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <!-- Header -->
    <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Vehicle Reports</h2>
            <a href="{{ route('admin.vehicle-reports.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Report
            </a>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reports as $report)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $report->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($report->vehicle)
                                <div class="text-sm text-gray-900">{{ $report->vehicle->make }} {{ $report->vehicle->model }}</div>
                                <div class="text-sm text-gray-500">{{ $report->vehicle->plate_number }}</div>
                            @else
                                <div class="text-sm text-gray-500">Vehicle not found</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($report->driver)->name ?? 'Driver not found' }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $report->title ?? 'No title' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if(!$report->type) bg-gray-100 text-gray-800
                                @elseif($report->type === 'mechanical') bg-blue-100 text-blue-800
                                @elseif($report->type === 'electrical') bg-yellow-100 text-yellow-800
                                @elseif($report->type === 'body_damage') bg-red-100 text-red-800
                                @elseif($report->type === 'tire') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $report->type ? ucfirst(str_replace('_', ' ', $report->type)) : 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if(!$report->severity) bg-gray-100 text-gray-800
                                @elseif($report->severity === 'high') bg-red-100 text-red-800
                                @elseif($report->severity === 'medium') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $report->severity ? ucfirst($report->severity) : 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if(!$report->status) bg-gray-100 text-gray-800
                                @elseif($report->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($report->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($report->status === 'resolved') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $report->status ? ucfirst(str_replace('_', ' ', $report->status)) : 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $report->created_at ? $report->created_at->format('Y-m-d H:i') : 'No date' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.vehicle-reports.show', $report) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                                View
                            </a>
                            
                            <div class="inline-block relative mr-2">
                                <button type="button" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-green-600 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" id="status-menu-button-{{ $report->id }}" aria-expanded="true" aria-haspopup="true" onclick="toggleDropdown('{{ $report->id }}')">
                                    Status
                                    <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10 hidden" id="status-dropdown-{{ $report->id }}" role="menu" aria-orientation="vertical" aria-labelledby="status-menu-button-{{ $report->id }}" tabindex="-1">
                                    <div class="py-1" role="none">
                                        <form action="{{ route('admin.vehicle-reports.update-status', $report) }}" method="POST" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="text-left w-full px-4 py-2 text-sm {{ $report->status === 'pending' ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }} hover:bg-gray-100" role="menuitem" tabindex="-1">Pending</button>
                                        </form>
                                        <form action="{{ route('admin.vehicle-reports.update-status', $report) }}" method="POST" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="text-left w-full px-4 py-2 text-sm {{ $report->status === 'in_progress' ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }} hover:bg-gray-100" role="menuitem" tabindex="-1">In Progress</button>
                                        </form>
                                        <form action="{{ route('admin.vehicle-reports.update-status', $report) }}" method="POST" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="text-left w-full px-4 py-2 text-sm {{ $report->status === 'resolved' ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }} hover:bg-gray-100" role="menuitem" tabindex="-1">Mark as Resolved</button>
                                        </form>
                                        
                                        @if($report->status === 'in_progress')
                                            <button type="button" 
                                                    onclick="openCompleteModal({{ $report->id }}); closeDropdown('{{ $report->id }}')" 
                                                    class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-gray-100 hover:text-green-900" 
                                                    role="menuitem" 
                                                    tabindex="-1">
                                                Complete with Details
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.vehicle-reports.update-status', $report) }}" method="POST" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="text-left w-full px-4 py-2 text-sm {{ $report->status === 'cancelled' ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }} hover:bg-gray-100" role="menuitem" tabindex="-1">Cancelled</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('admin.vehicle-reports.edit', $report) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                                Edit
                            </a>
                            <form action="{{ route('admin.vehicle-reports.destroy', $report) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-red-600 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this report?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No vehicle reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $reports->links() }}
    </div>
</div>

<script>
    function toggleDropdown(reportId) {
        const dropdown = document.getElementById(`status-dropdown-${reportId}`);
        const allDropdowns = document.querySelectorAll('[id^="status-dropdown-"]');
        
        // Close all other dropdowns
        allDropdowns.forEach(elem => {
            if (elem.id !== `status-dropdown-${reportId}`) {
                elem.classList.add('hidden');
            }
        });
        
        // Toggle the current dropdown
        dropdown.classList.toggle('hidden');
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const isDropdownButton = event.target.closest('[id^="status-menu-button-"]');
        if (!isDropdownButton) {
            const allDropdowns = document.querySelectorAll('[id^="status-dropdown-"]');
            allDropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });
</script>

<!-- Complete Report Modal -->
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
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Complete Vehicle Report</h3>
                        <form id="complete-form" action="" method="POST" class="mt-4">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes <span class="text-red-500">*</span></label>
                                    <textarea id="resolution_notes" name="resolution_notes" rows="3" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                </div>
                                <div>
                                    <label for="parts_used" class="block text-sm font-medium text-gray-700">Parts Used</label>
                                    <input type="text" id="parts_used" name="parts_used" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="number" step="0.5" min="0" max="1000" id="labor_hours" name="labor_hours" required
                                                   class="block w-full rounded-md border-gray-300 pr-12 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">hrs</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">ETB</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" id="total_cost" name="total_cost" required
                                                   class="block w-full rounded-md border-gray-300 pl-12 pr-12 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="completion_date" class="block text-sm font-medium text-gray-700">Completion Date <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" id="completion_date" name="completion_date" 
                                           value="{{ now()->format('Y-m-d\TH:i') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                    Complete Report
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

@push('scripts')
<script>
    function openCompleteModal(reportId) {
        const modal = document.getElementById('complete-modal');
        const form = document.getElementById('complete-form');
        form.action = `/fleet/admin/vehicle-reports/${reportId}/complete`;
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

@endsection
