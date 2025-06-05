@props(['notifications' => [], 'unreadCount' => 0])

<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <!-- Notification Button -->
    <button @click="open = !open" class="relative p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Unread Badge -->
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
         class="origin-top-right absolute right-0 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none">
        
        <!-- Header -->
        <div class="px-4 py-3">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                @if($unreadCount > 0)
                    @php
                        $markAllAsReadRoute = auth()->user()->hasRole('admin') ? 'admin.notifications.mark-all-as-read' :
                            (auth()->user()->hasRole('driver') ? 'driver.notifications.mark-all-as-read' :
                            (auth()->user()->hasRole('maintenance_staff') ? 'maintenance.notifications.mark-all-as-read' :
                            'client.notifications.mark-all-as-read'));
                    @endphp
                    <form action="{{ route($markAllAsReadRoute) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div class="relative px-4 py-3 hover:bg-gray-50 transition duration-150 ease-in-out {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="{{ $notification->getIconClass() }}">
                                {!! $notification->getIconSvg() !!}
                            </div>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm text-gray-900">{{ $notification->message }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                            
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                    View details
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <div class="ml-4 flex flex-col space-y-1">
                            @php
                                $markAsReadRoute = auth()->user()->hasRole('admin') ? 'admin.notifications.mark-as-read' :
                                    (auth()->user()->hasRole('driver') ? 'driver.notifications.mark-as-read' :
                                    (auth()->user()->hasRole('maintenance_staff') ? 'maintenance.notifications.mark-as-read' :
                                    'client.notifications.mark-as-read'));
                                
                                $deleteRoute = auth()->user()->hasRole('admin') ? 'admin.notifications.destroy' :
                                    (auth()->user()->hasRole('driver') ? 'driver.notifications.destroy' :
                                    (auth()->user()->hasRole('maintenance_staff') ? 'maintenance.notifications.destroy' :
                                    'client.notifications.destroy'));
                            @endphp
                            
                            @if(is_null($notification->read_at))
                                <form action="{{ route($markAsReadRoute, $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-800" title="Mark as read">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route($deleteRoute, $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete notification">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-sm text-center text-gray-500">
                    No notifications
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 text-sm border-t border-gray-200">
            <div class="flex justify-between items-center">
                @php
                    $notificationsIndexRoute = auth()->user()->hasRole('admin') ? 'admin.notifications.index' :
                        (auth()->user()->hasRole('driver') ? 'driver.notifications.index' :
                        (auth()->user()->hasRole('maintenance_staff') ? 'maintenance.notifications.index' :
                        'client.notifications.index'));
                    
                    $clearAllRoute = auth()->user()->hasRole('admin') ? 'admin.notifications.clear-all' :
                        (auth()->user()->hasRole('driver') ? 'driver.notifications.clear-all' :
                        (auth()->user()->hasRole('maintenance_staff') ? 'maintenance.notifications.clear-all' :
                        'client.notifications.clear-all'));
                @endphp
                
                <a href="{{ route($notificationsIndexRoute) }}" class="font-medium text-blue-600 hover:text-blue-800">
                    View all notifications
                </a>
                
                @if(count($notifications) > 0)
                    <form action="{{ route($clearAllRoute) }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                            Clear all
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationDropdown', () => ({
        open: false,
        notifications: [],
        unreadCount: 0,

        init() {
            this.fetchNotifications();
            this.initializeEcho();
        },

        fetchNotifications() {
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                });
        },

        markAsRead(id) {
            fetch(`/api/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(() => {
                this.notifications = this.notifications.map(notification => {
                    if (notification.id === id) {
                        notification.read_at = new Date().toISOString();
                    }
                    return notification;
                });
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            });
        },

        markAllAsRead() {
            fetch('/api/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(() => {
                this.notifications = this.notifications.map(notification => {
                    notification.read_at = new Date().toISOString();
                    return notification;
                });
                this.unreadCount = 0;
            });
        },

        initializeEcho() {
            Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    this.notifications.unshift(notification);
                    if (is_null(notification.read_at)) {
                        this.unreadCount++;
                    }
                });
        }
    }));
});
</script>
@endpush 