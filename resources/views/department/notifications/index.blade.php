@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Notifications</h3>
                @if($notifications->whereNull('read_at')->count() > 0)
                    <form action="{{ route('client.notifications.mark-all-as-read') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            @forelse($notifications as $notification)
            <div class="px-4 py-5 sm:px-6 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }} border-b">
                <div class="flex items-start space-x-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $notification->data['message'] ?? 'Notification' }}
                        </p>
                        
                        @if(isset($notification->data['data']))
                            <div class="mt-2 text-sm text-gray-500">
                                @foreach($notification->data['data'] as $key => $value)
                                    @if(!is_array($value))
                                        <p><span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2 text-xs text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        @if(is_null($notification->read_at))
                            <form action="{{ route('client.notifications.mark-as-read', $notification) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if(isset($notification->data['link']))
                    <div class="mt-3">
                        <a href="{{ $notification->data['link'] }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View Details →
                        </a>
                    </div>
                @endif
            </div>
            @empty
            <div class="px-4 py-5 sm:px-6 text-center text-gray-500">
                No notifications found.
            </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="px-4 py-3 bg-gray-50 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 