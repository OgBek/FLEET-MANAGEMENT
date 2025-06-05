@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">User Details</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit User
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" 
                              method="POST" 
                              class="inline-block"
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="mt-1">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Department</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->department->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Additional Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Approval Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->approval_status === 'approved') bg-green-100 text-green-800
                                    @elseif($user->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($user->approval_status) }}
                                </span>
                            </dd>
                        </div>
                        @if($user->license_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">License Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->license_number }}</dd>
                            </div>
                        @endif
                        @if($user->specialization)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->specialization }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F j, Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('F j, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Activity Information -->
                <div class="bg-gray-50 rounded-lg p-4 md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($user->hasRole('driver'))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigned Bookings</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->bookingsAssigned->count() }}</dd>
                            </div>
                        @endif
                        @if($user->hasRole('maintenance_staff'))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Maintenance Records</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->maintenanceRecords->count() }}</dd>
                            </div>
                        @endif
                        @if($user->hasAnyRole(['department_head', 'department_staff']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested Bookings</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->bookingsRequested->count() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection 