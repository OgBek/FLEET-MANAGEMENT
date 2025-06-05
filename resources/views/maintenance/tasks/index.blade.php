@extends('layouts.dashboard')

@section('header')
    Maintenance Tasks
@endsection

@section('content')
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Maintenance Tasks</h2>
            </div>

            @if($tasks->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
                    <p class="mt-1 text-sm text-gray-500">You don't have any maintenance tasks assigned to you yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tasks as $task)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $task->vehicle->registration_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $task->vehicle->formatted_brand }} {{ $task->vehicle->model }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $task->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($task->description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->status === 'completed') bg-green-100 text-green-800
                                            @elseif($task->status === 'overdue') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($task->status === 'overdue')
                                                In Progress
                                            @else
                                                {{ ucfirst($task->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $task->scheduled_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(isset($task->type) && $task->type == 'schedule')
                                            <a href="{{ route('maintenance.schedules.show', $task->schedule_id) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                            
                                            @if($task->status === 'pending')
                                                <form action="{{ route('maintenance.schedules.start', $task->schedule_id) }}" method="POST" class="inline ml-3">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Start Work</button>
                                                </form>
                                            @elseif($task->status === 'in_progress')
                                                <button onclick="openCompleteModal('{{ $task->schedule_id }}', 'schedule')" 
                                                        class="text-green-600 hover:text-green-900 ml-3">Mark Complete</button>
                                            @endif
                                        @elseif(isset($task->type) && $task->type == 'service_request')
                                            <a href="{{ route('maintenance.service-requests.show', $task->request_id) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                            
                                            @if($task->status === 'approved')
                                                <form action="{{ route('maintenance.service-requests.start', $task->request_id) }}" method="POST" class="inline ml-3">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Start Work</button>
                                                </form>
                                            @elseif($task->status === 'in_progress')
                                                <button onclick="openCompleteModal('{{ $task->request_id }}', 'service_request')" 
                                                        class="text-green-600 hover:text-green-900 ml-3">Complete Request</button>
                                            @endif
                                        @else
                                            <a href="{{ route('maintenance.tasks.show', $task->id) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Complete Task Modal -->
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Complete Task</h3>
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
                                        <input type="number" id="labor_hours" name="labor_hours" step="1" min="1"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost (Birr)</label>
                                        <input type="number" id="total_cost" name="total_cost" step="100" min="0" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-sm text-gray-500">Include both parts and labor costs</p>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                                        Complete Task
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
        function openCompleteModal(id, type) {
            const modal = document.getElementById('complete-modal');
            const form = document.getElementById('complete-form');
            
            // Set the form action based on type
            if (type === 'schedule') {
                form.action = `/fleet/maintenance/schedules/${id}/complete`;
            } else if (type === 'service_request') {
                form.action = `/fleet/maintenance/service-requests/${id}/complete`;
            }
            
            modal.classList.remove('hidden');
        }

        function closeCompleteModal() {
            const modal = document.getElementById('complete-modal');
            modal.classList.add('hidden');
            
            // Reset form
            document.getElementById('complete-form').reset();
        }
    </script>
    @endpush
@endsection