@extends('layouts.dashboard')

@section('header')
    Service Request Details
@endsection

@section('content')
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Service Request Details</h2>
                <div class="space-x-3">
                    @if($serviceRequest->status === 'approved' || $serviceRequest->status === 'in_progress')
                        @if($serviceRequest->status === 'approved')
                            <form action="{{ route('maintenance.service-requests.start', $serviceRequest) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Start Work
                                </button>
                            </form>
                        @endif
                        @if($serviceRequest->status === 'in_progress')
                            <button onclick="document.getElementById('complete-form').classList.toggle('hidden')" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Mark as Complete
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($serviceRequest->status === 'completed') bg-green-100 text-green-800
                                @elseif($serviceRequest->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($serviceRequest->status) }}
                            </span>
                        </dd>
                    </div>

                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $serviceRequest->vehicle->registration_number }} - 
                            {{ $serviceRequest->vehicle->formatted_brand }} {{ $serviceRequest->vehicle->model }}
                        </dd>
                    </div>

                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Issue Title</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $serviceRequest->issue_title }}
                        </dd>
                    </div>

                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Issue Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $serviceRequest->issue_description }}
                        </dd>
                    </div>

                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Requested By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $serviceRequest->requestedBy->name }} ({{ $serviceRequest->requestedBy->department->name }})
                        </dd>
                    </div>

                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                        <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $serviceRequest->scheduled_date->format('F d, Y') }}
                        </dd>
                    </div>

                    @if($serviceRequest->completed_at)
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $serviceRequest->completed_at->format('F d, Y H:i A') }}
                            </dd>
                        </div>

                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500">Resolution Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $serviceRequest->resolution_notes }}
                            </dd>
                        </div>

                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500">Parts Used</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $serviceRequest->parts_used ?: 'None' }}
                            </dd>
                        </div>

                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500">Labor Hours</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $serviceRequest->labor_hours }} hours
                            </dd>
                        </div>

                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500">Total Cost</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                Birr {{ number_format($serviceRequest->total_cost, 2) }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

                    @if($serviceRequest->maintenanceTasks->isNotEmpty())
                        <div class="py-4 sm:py-5">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Related Maintenance Tasks</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <ul class="divide-y divide-gray-200">
                                        @foreach($serviceRequest->maintenanceTasks as $task)
                                            <li class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-blue-600 truncate">
                                                            {{ $task->title }}
                                                        </p>
                                                        @if($task->description)
                                                            <p class="mt-1 text-sm text-gray-500">
                                                                {{ $task->description }}
                                                            </p>
                                                        @endif
                                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                                            <div>
                                                                <span class="font-medium">Scheduled:</span> 
                                                                {{ $task->scheduled_date->format('M d, Y H:i') }}
                                                            </div>
                                                            <span>•</span>
                                                            <div class="flex items-center">
                                                                <span class="mr-1">Priority:</span>
                                                                @php
                                                                    $priorityClasses = [
                                                                        'low' => 'text-green-800 bg-green-100',
                                                                        'medium' => 'text-yellow-800 bg-yellow-100',
                                                                        'high' => 'text-orange-800 bg-orange-100',
                                                                        'urgent' => 'text-red-800 bg-red-100',
                                                                    ][$task->priority] ?? 'bg-gray-100 text-gray-800';
                                                                @endphp
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityClasses }}">
                                                                    {{ ucfirst($task->priority) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($task->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                            @else bg-gray-100 text-gray-800
                                                            @endif">
                                                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if($serviceRequest->status !== 'completed')
                <div id="complete-form" class="hidden mt-6 border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Complete Service Request</h3>
                    <form action="{{ route('maintenance.service-requests.complete', $serviceRequest) }}" method="POST">
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
                            <div class="flex justify-end space-x-3">
                                <button type="button" 
                                        onclick="this.closest('div[id]').classList.add('hidden')"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    Complete Service Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('maintenance.service-requests.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Service Requests
        </a>
    </div>
@endsection