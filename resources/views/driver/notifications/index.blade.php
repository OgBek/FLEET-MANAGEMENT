@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Notifications</h3>
            </div>

            @forelse($notifications as $notification)
            <div class="p-4 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                <div class="flex items-start space-x-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $notification->data['message'] ?? 'Notification' }}
                        </p>
                        
                        @if(isset($notification->data['data']))
                            <div class="mt-2 text-sm text-gray-500">
                                @foreach($notification->data['data'] as $key => $value)
                                    @if(!is_array($value) && !is_null($value))
                                        <p><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex items-center space-x-4">
                        @if(is_null($notification->read_at))
                            <form action="{{ route('driver.notifications.mark-as-read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                        
                        @if(isset($notification->data['link']))
                            <a href="{{ route('driver.notifications.show', $notification->id) }}" class="text-blue-600 hover:text-blue-800">
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
</div>
@endsection 