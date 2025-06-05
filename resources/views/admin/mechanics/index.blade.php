@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Mechanics</h2>
                <a href="{{ route('admin.mechanics.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Mechanic
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Mechanics</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_mechanics'] }}</dd>
                </div>
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Tasks</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['active_tasks'] }}</dd>
                </div>
                <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Tasks</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['completed_tasks'] }}</dd>
                </div>
            </dl>
        </div>

        <!-- Mechanics Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact Info
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Specialization
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tasks
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mechanics as $mechanic)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $mechanic->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $mechanic->email }}</div>
                                <div class="text-sm text-gray-500">{{ $mechanic->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $mechanic->specialization }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium">Active:</span> {{ $mechanic->maintenanceSchedules->where('status', 'pending')->count() }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Completed:</span> {{ $mechanic->maintenanceRecords->where('status', 'completed')->count() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.mechanics.show', $mechanic) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    <a href="{{ route('admin.mechanics.edit', $mechanic) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    @if($mechanic->id !== auth()->id())
                                        <form action="{{ route('admin.mechanics.destroy', $mechanic) }}" 
                                              method="POST" 
                                              class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete this mechanic?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No mechanics found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection 