@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
        @if($notifications->isNotEmpty())
            <form action="{{ route('client.notifications.mark-all-as-read') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @forelse($notifications as $notification)
            <div class="p-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }} border-b">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-gray-800">{{ $notification->data['message'] ?? 'No message available' }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @if(!$notification->read_at)
                        <form action="{{ route('client.notifications.mark-as-read', $notification->id) }}" method="POST" class="ml-4">
                            @csrf
                            <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                Mark as Read
                            </button>
                        </form>
                    @endif
                </div>
                @if(isset($notification->data['link']))
                    <a href="{{ route('client.notifications.show', $notification->id) }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-800">
                        View Details →
                    </a>
                @endif
            </div>
        @empty
            <div class="p-4 text-center text-gray-500">
                No notifications found.
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection 