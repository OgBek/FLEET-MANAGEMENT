@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex items-center justify-between">
        <h3 class="text-gray-700 text-3xl font-medium">Notifications</h3>
        @if($unreadCount > 0)
            <form action="{{ route('admin.notifications.mark-all-as-read') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="mt-8">
        @forelse($notifications as $notification)
            <div class="mb-4 p-4 bg-white rounded-lg shadow {{ !$notification->read_at ? 'border-l-4 border-blue-500' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ ucfirst($notification->data['type'] ?? 'Notification') }}
                            </h4>
                            @if(!$notification->read_at)
                                <span class="ml-2 px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                    New
                                </span>
                            @endif
                        </div>
                        
                        <p class="mt-1 text-gray-600">{{ $notification->data['message'] }}</p>
                        
                        @if(isset($notification->data['data']))
                            <div class="mt-2 grid grid-cols-2 gap-4 text-sm text-gray-500">
                                @foreach($notification->data['data'] as $key => $value)
                                    @if(!is_array($value) && !is_null($value))
                                        <div>
                                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="ml-4 flex-shrink-0 flex">
                        @if(!$notification->read_at)
                            <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="mr-2">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                        
                        @if(isset($notification->data['link']))
                            <a href="{{ $notification->data['link'] }}" class="text-blue-600 hover:text-blue-800">
                                View details
                                @if(isset($notification->data['status']) && $notification->data['status'] === 'resolved')
                                    <span class="ml-1 text-xs text-green-600">(Resolved)</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any notifications at the moment.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection 