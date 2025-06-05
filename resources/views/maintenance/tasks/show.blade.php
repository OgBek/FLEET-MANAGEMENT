@extends('layouts.dashboard')

@section('header')
    Maintenance Task Details
@endsection

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Maintenance Task Details</h2>
            <div class="flex space-x-2">
                @if($task->status === 'pending')
                    <form action="{{ route('maintenance.tasks.start', $task) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Start Task
                        </button>
                    </form>
                @elseif($task->status === 'in_progress')
                    <button type="button" onclick="openCompleteModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Mark as Completed
                    </button>
                @endif
                <a href="{{ route('maintenance.tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Tasks
                </a>
            </div>
        </div>

        <!-- Task Information -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Task Information</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->status === 'completed') bg-green-100 text-green-800
                                @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($task->isDue()) bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                            @if($task->status === 'completed')
                                <p class="mt-1 text-sm text-gray-500">Completed on {{ $task->completed_at->format('M d, Y H:i') }}</p>
                            @elseif($task->status === 'in_progress')
                                <p class="mt-1 text-sm text-gray-500">Started on {{ $task->started_at->format('M d, Y H:i') }}</p>
                            @elseif($task->isDue())
                                <p class="mt-1 text-sm text-red-600">Overdue since {{ $task->scheduled_date->format('M d, Y') }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Scheduled for {{ $task->scheduled_date->format('M d, Y') }}</p>
                            @endif
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Maintenance Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ ucwords(str_replace('_', ' ', $task->maintenance_type)) }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->description }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->priority === 'high') bg-red-100 text-red-800
                                @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Estimated Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->estimated_hours }} hours
                        </dd>
                    </div>
                    @if($task->assigned_to)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $task->assignedStaff ? $task->assignedStaff->name : 'Not assigned' }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Vehicle Information</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $task->vehicle->registration_number }}
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Make & Model</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $task->vehicle->make }} {{ $task->vehicle->model }}
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Year</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $task->vehicle->year }}
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Mileage</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ number_format($task->vehicle->current_mileage) }} km
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        @if($task->status === 'completed')
        <!-- Completion Details -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Completion Details</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Completed By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->completedBy->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Completion Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->completed_at->format('M d, Y H:i') }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Resolution Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->resolution_notes }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Parts Used</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $task->parts_used ?: 'None' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Labor Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ number_format($task->labor_hours, 1) }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Total Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ number_format($task->total_cost, 2) }} Birr
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif

        @if($task->status !== 'completed')
        <!-- Task Actions -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Task Actions</h3>
                <div class="space-x-4">
                    @if($task->status === 'pending')
                        <form action="{{ route('maintenance.tasks.start', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                                Start Task
                            </button>
                        </form>
                    @endif
                    
                    @if($task->status === 'in_progress')
                        <button type="button" 
                                onclick="document.getElementById('completeTaskModal').classList.remove('hidden')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            Complete Task
                        </button>
                    @endif
                    
                    <a href="{{ route('maintenance.tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Edit Task
                    </a>
                </div>
            </div>
        </div>
        @endif
        </div>
    </div>

    <!-- Complete Task Modal -->
    <div id="completeTaskModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" aria-hidden="true">
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <form id="completeTaskForm" method="POST" action="{{ route('maintenance.tasks.complete', $task) }}" class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 w-full">
                    @csrf
                    
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Complete Maintenance Task</h3>
                            
                            @error('labor_hours')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-4">
                                <div class="mb-4">
                                    <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes</label>
                                    <div class="mt-1">
                                        <textarea id="resolution_notes" name="resolution_notes" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required></textarea>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="parts_used" class="block text-sm font-medium text-gray-700">Parts Used</label>
                                    <div class="mt-1">
                                        <input type="text" id="parts_used" name="parts_used" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours</label>
                                    <div class="mt-1">
                                        <input type="number" id="labor_hours" name="labor_hours" step="0.5" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost (BIRR)</label>
                                    <div class="mt-1">
                                        <input type="number" id="total_cost" name="total_cost" step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Include both parts and labor costs</p>
                                </div>
                            </div>
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate total cost when labor hours changes
            const laborHoursInput = document.getElementById('labor_hours');
            const totalCostInput = document.getElementById('total_cost');
            const form = document.getElementById('completeTaskForm');

            function calculateTotalCost() {
                const laborHours = parseFloat(laborHoursInput.value) || 0;
                // Assuming a fixed labor rate of 500 Birr per hour
                const laborRate = 500;
                const total = laborHours * laborRate;
                totalCostInput.value = total.toFixed(2);
            }

            if (laborHoursInput && totalCostInput) {
                laborHoursInput.addEventListener('input', calculateTotalCost);
                
                // Initialize total cost on page load
                calculateTotalCost();
            }

            // Handle form submission
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    
                    try {
                        // Disable submit button and show loading state
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.innerHTML = `
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...`;
                        }
                        
                        // Submit form data using fetch API
                        const formData = new FormData(form);
                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });
                            
                            const data = await response.json();
                            
                            if (response.ok) {
                                // Show success message
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                // Show validation errors
                                if (data.errors) {
                                    // Clear previous errors
                                    document.querySelectorAll('.text-red-600').forEach(el => el.remove());
                                    
                                    // Show new errors
                                    Object.entries(data.errors).forEach(([field, messages]) => {
                                        const input = document.querySelector(`[name="${field}"]`);
                                        if (input) {
                                            const errorElement = document.createElement('p');
                                            errorElement.className = 'mt-1 text-sm text-red-600';
                                            errorElement.textContent = Array.isArray(messages) ? messages[0] : messages;
                                            input.parentNode.insertBefore(errorElement, input.nextSibling);
                                        }
                                    });
                                    
                                    // Scroll to first error
                                    const firstError = document.querySelector('.text-red-600');
                                    if (firstError) {
                                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                } else {
                                    // Show general error message
                                    alert(data.message || 'An error occurred while completing the task. Please try again.');
                                }
                            }
                        } catch (error) {
                            console.error('Fetch error:', error);
                            alert('Network error. Please check your connection and try again.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while completing the task. Please try again.');
                    } finally {
                        // Re-enable submit button
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonText;
                        }
                    }
                });
            }
        });

        function openCompleteModal() {
            document.getElementById('completeTaskModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.body.style.paddingRight = '0';
        }

        function closeCompleteModal() {
            document.getElementById('completeTaskModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.body.style.paddingRight = '';
            
            // Reset form when closing
            const form = document.getElementById('completeTaskForm');
            if (form) {
                form.reset();
                // Recalculate total cost after reset
                const laborHoursInput = document.getElementById('labor_hours');
                if (laborHoursInput) {
                    laborHoursInput.dispatchEvent(new Event('input'));
                }
            }
            
            // Clear any error messages
            document.querySelectorAll('.text-red-600').forEach(el => el.remove());
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('completeTaskModal');
            if (event.target === modal) {
                closeCompleteModal();
            }
        }
    </script>
    @endpush
    
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // If there are validation errors, open the modal
                const modal = document.getElementById('completeTaskModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    
                    // Scroll to first error
                    const firstError = document.querySelector('.text-red-600');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        </script>
    @endif
@endsection