@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <!-- Header -->
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Department Details</h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.departments.edit', $department) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Edit Department
                        </a>
                        <a href="{{ route('admin.departments.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $department->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $department->description ?? 'No description provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $department->created_at->format('F j, Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $department->updated_at->format('F j, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Department Statistics -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Department Statistics</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $department->users->count() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Bookings</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $department->bookings->count() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Active Bookings</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $department->getActiveBookings()->count() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Recent Users -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Users</h4>
                        @if($department->users->isNotEmpty())
                            <ul class="divide-y divide-gray-200">
                                @foreach($department->users->take(5) as $user)
                                    <li class="py-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @foreach($user->roles as $role)
                                                    bg-blue-100 text-blue-800
                                                @endforeach">
                                                {{ $user->roles->first()->name ?? 'No Role' }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">No users in this department.</p>
                        @endif
                    </div>

                    <!-- Recent Bookings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h4>
                        @if($department->bookings->isNotEmpty())
                            <ul class="divide-y divide-gray-200">
                                @foreach($department->bookings->take(5) as $booking)
                                    <li class="py-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $booking->vehicle->brand->name }} {{ $booking->vehicle->model }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $booking->start_time->format('M d, Y H:i') }} - {{ $booking->end_time->format('M d, Y H:i') }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($booking->status === 'approved') bg-green-100 text-green-800
                                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">No bookings for this department.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 