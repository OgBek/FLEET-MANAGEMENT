@props(['align' => 'right', 'width' => '96'])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'left-0';
        break;
    case 'right':
    default:
        $alignmentClasses = 'right-0';
        break;
}

switch ($width) {
    case '48':
        $width = 'w-48';
        break;
    case '96':
        $width = 'w-96';
        break;
    default:
        $width = 'w-96';
        break;
}

$user = auth()->user();
$notifications = $user->notifications()->latest()->take(5)->get();
$unreadCount = $user->unreadNotifications()->count();

// Determine routes based on user role
$routePrefix = 'client';
if ($user->hasRole('admin')) {
    $routePrefix = 'admin';
} elseif ($user->hasRole('driver')) {
    $routePrefix = 'driver';
} elseif ($user->hasRole('maintenance_staff')) {
    $routePrefix = 'maintenance';
}
@endphp

<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <!-- Notification Button -->
    <button @click="open = !open" class="relative p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 -mt-1 -mr-1 flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-xs font-medium text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Panel -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute {{ $alignmentClasses }} mt-2 {{ $width }} rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50">
        
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-gray-900">Notifications</h3>
                @if($unreadCount > 0)
                    <form action="{{ route($routePrefix . '.notifications.mark-all-as-read') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($notifications as $notification)
                    <div class="flex items-start space-x-3 {{ is_null($notification->read_at) ? 'bg-blue-50 -mx-4 px-4 py-2' : '' }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                            @if(isset($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-block">
                                    View Details →
                                </a>
                            @endif
                        </div>
                        @if(is_null($notification->read_at))
                            <form action="{{ route($routePrefix . '.notifications.mark-as-read', $notification) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No notifications</p>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="{{ route($routePrefix . '.notifications.index') }}" class="block text-center text-sm text-blue-600 hover:text-blue-800">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
</div>