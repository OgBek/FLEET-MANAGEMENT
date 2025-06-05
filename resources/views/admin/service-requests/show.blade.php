@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Service Request Details</h1>
            <div class="flex space-x-3">
                @if($serviceRequest->status === 'pending')
                    <button onclick="document.getElementById('approve-form').classList.toggle('hidden')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve
                    </button>
                    
                    <button onclick="document.getElementById('reject-form').classList.toggle('hidden')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject
                    </button>
                @endif
                <a href="{{ route('admin.service-requests.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Approval Form -->
    <div id="approve-form" class="hidden mb-8 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Approve Service Request</h2>
        <form action="{{ route('admin.service-requests.approve', $serviceRequest) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign to Maintenance Staff</label>
                    <select id="assigned_to" name="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Staff</option>
                        @foreach($maintenanceStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700">Notes for Staff</label>
                    <textarea id="admin_notes" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('approve-form').classList.add('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Approve Request
                </button>
            </div>
        </form>
    </div>

    <!-- Rejection Form -->
    <div id="reject-form" class="hidden mb-8 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Reject Service Request</h2>
        <form action="{{ route('admin.service-requests.reject', $serviceRequest) }}" method="POST">
            @csrf
            <div class="sm:col-span-6">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection</label>
                <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('reject-form').classList.add('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Reject Request
                </button>
            </div>
        </form>
    </div>

    <!-- Service Request Details -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Request Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($serviceRequest->status === 'completed') bg-green-100 text-green-800
                            @elseif($serviceRequest->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($serviceRequest->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Priority</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($serviceRequest->priority === 'urgent') bg-red-100 text-red-800
                            @elseif($serviceRequest->priority === 'high') bg-orange-100 text-orange-800
                            @elseif($serviceRequest->priority === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($serviceRequest->priority) }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Requested By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->requestedBy->name }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceRequest->scheduled_date ? $serviceRequest->scheduled_date->format('M d, Y') : 'Not Scheduled' }}
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Issue Title</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->issue_title }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Issue Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->issue_description }}</dd>
                </div>

                @if($serviceRequest->additional_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->additional_notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Vehicle Information -->
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Vehicle Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->registration_number }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Model</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->model }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Brand</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $serviceRequest->vehicle->formatted_brand }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Current Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($serviceRequest->vehicle->status === 'available') bg-green-100 text-green-800
                            @elseif($serviceRequest->vehicle->status === 'maintenance') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($serviceRequest->vehicle->status) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection 