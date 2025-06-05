<!DOCTYPE html>
<?php
// Check if user is from admin department for JavaScript validation
$isAdminDepartment = false;
if (auth()->check() && auth()->user()->department) {
    $deptName = strtolower(auth()->user()->department->name);
    $isAdminDepartment = $deptName === 'administration' || $deptName === 'admin';
}
?>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dilla University Fleet Management System') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- User ID for notifications -->
    <script>
        const userId = {{ auth()->id() }};
    </script>

    <!-- Notification Scripts -->
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(() => {
                        this.notifications = this.notifications.map(notification => {
                            if (notification.id === id) {
                                notification.is_read = true;
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(() => {
                        this.notifications = this.notifications.map(notification => {
                            notification.is_read = true;
                            return notification;
                        });
                        this.unreadCount = 0;
                    });
                },

                initializeEcho() {
                    if (window.Echo) {
                        Echo.private(`App.Models.User.${userId}`)
                            .notification((notification) => {
                                this.notifications.unshift(notification);
                                if (!notification.is_read) {
                                    this.unreadCount++;
                                }
                            });
                    }
                }
            }));
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
        .sidebar-nav {
            height: calc(100vh - 4rem);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="font-sans antialiased h-full bg-gray-100" data-is-admin="{{ $isAdminDepartment ? 'true' : 'false' }}">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 fixed w-full z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="flex items-center">
                                <img src="{{ asset('dilla.png') }}" alt="Dilla University Logo" class="h-12 w-auto object-contain ml-[-10px] mr-2" style="max-width: 70px;">
                                <span class="text-xl font-bold text-gray-800">{{ config('app.name', 'Dilla University Fleet Management System') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="relative ml-3">
                            <!-- Multi-Session Indicator -->
                            @if(Session::has('multi_auth_admin') || 
                                Session::has('multi_auth_department_head') || 
                                Session::has('multi_auth_department_staff') || 
                                Session::has('multi_auth_driver') || 
                                Session::has('multi_auth_maintenance_staff'))
                                <a href="{{ route('session.selector') }}" class="inline-flex items-center px-3 py-1 mr-3 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <span>
                                        {{ ucfirst(str_replace('_', ' ', auth()->user()->getRoleNames()->first())) }} Session
                                    </span>
                                </a>
                            @endif
                            
                            <!-- Notifications -->
                            <x-notification-menu align="right" width="96" />
                        </div>

                        <!-- Profile dropdown -->
                        <div x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false" class="relative">
                            <button type="button" 
                                    @click="open = !open" 
                                    class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <img class="h-8 w-8 rounded-full object-cover mr-2" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                    <svg class="fill-current h-4 w-4 transition-transform duration-200"
                                         :class="{'rotate-180': open}" 
                                         xmlns="http://www.w3.org/2000/svg" 
                                         viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>

                            <div x-cloak
                                 x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                                @php
                                    $profileRoute = 'client.profile.edit';
                                    if (auth()->user()->hasRole('admin')) {
                                        $profileRoute = 'admin.profile.edit';
                                    } elseif (auth()->user()->hasRole('driver')) {
                                        $profileRoute = 'driver.profile.edit';
                                    } elseif (auth()->user()->hasRole('maintenance_staff')) {
                                        $profileRoute = 'maintenance.profile.edit';
                                    }
                                @endphp
                                <a href="{{ route($profileRoute) }}" 
                                   class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('*.profile.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </div>
                                </a>
                                
                                <!-- Multi-session Manager Link -->
                                <a href="{{ route('session.selector') }}" class="block py-2.5 px-4 rounded transition duration-200 text-gray-700 hover:bg-gray-100 border-t border-gray-100 mt-1 pt-1">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        Multi-session Manager
                                    </div>
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 pt-16 bg-white w-64 border-r border-gray-200">
            <div class="sidebar-nav px-4 py-4">
                @if(auth()->user()->hasRole('admin'))
                    <!-- Admin Sidebar -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </div>
                    </a>

                  

                    <!-- Profile Link -->
                    <a href="{{ route('admin.profile.edit') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.profile.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile</span>
                        </div>
                    </a>

                    <!-- Vehicles Section -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button type="button" 
                                @click="open = !open" 
                                class="flex items-center justify-between w-full py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.vehicles.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Vehicles
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" 
                                 :class="{'rotate-180': open}" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-cloak 
                             x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="mt-2 space-y-1 px-7">
                            <a href="{{ route('admin.vehicles.index') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.vehicles.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                                All Vehicles
                            </a>
                            <a href="{{ route('admin.vehicles.create') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.vehicles.create') ? 'bg-blue-50 text-blue-600' : '' }}">
                                Add Vehicle
                            </a>
                            <a href="{{ route('admin.vehicle-categories.index') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.vehicle-categories.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                Categories
                            </a>
                            <a href="{{ route('admin.vehicle-reports.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.vehicle-reports.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Vehicle Reports
                            </a>
                            <a href="{{ route('admin.vehicle-brands.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.vehicle-brands.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Vehicle Brands
                        </a>
                        </div>
                    </div>

                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition duration-200 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.departments.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Staff
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-cloak 
                         x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="mt-2 space-y-1 px-7">
                        <a href="{{ route('admin.users.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.users.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                            All Staff
                        </a>
                        <a href="{{ route('admin.users.create') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.users.create') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Add Staff
                        </a>
                        <a href="{{ route('admin.departments.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.departments.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Departments
                        </a>
                    </div>
                </div>


                    <a href="{{ route('admin.bookings.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.bookings.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Bookings
                        </div>
                    </a>

                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition duration-200 {{ request()->routeIs('admin.maintenance.*') || request()->routeIs('admin.maintenance-schedules.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Maintenance
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-cloak 
                         x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="mt-2 space-y-1 px-7">
                        <a href="{{ route('admin.maintenance-schedules.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.maintenance-schedules.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Maintenance Records
                        </a>
                        
                        <a href="{{ route('admin.service-requests.index') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('admin.service-requests.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                            Service Requests
                        </a>
                       
                    </div>
                </div>
  
                  
                        <a href="{{ route('admin.feedback.index') }}"  
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.feedback.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                         Feedback
                        </div>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Reports
                        </div>
                    </a>

                    <!-- Notifications -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.notifications.index') }}" 
                           class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span>Notifications</span>
                                </div>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                    $totalCount = auth()->user()->notifications()->count();
                                @endphp
                                <div class="flex items-center">
                                    @if($totalCount > 0)
                                        <form action="{{ route('admin.notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')" class="mr-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800" title="Clear all notifications">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>

                @elseif(auth()->user()->hasRole(['department_head', 'department_staff']))
                    <!-- Department Head/Staff Sidebar -->
                    <a href="{{ route('client.dashboard') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.dashboard') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </div>
                    </a>

                    <!-- Profile Link -->
                    <a href="{{ route('client.profile.edit') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.profile.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile</span>
                        </div>
                    </a>

                    <a href="{{ route('client.bookings.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.bookings.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            My Bookings
                        </div>
                    </a>

                    <a href="{{ route('client.vehicles.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.vehicles.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Available Vehicles
                        </div>
                    </a>

                    <a href="{{ route('client.feedback.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.feedback.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <span>Feedback</span>
                        </div>
                    </a>

                    @if(auth()->user()->hasRole('department_head'))
                        @php
                            // Only show bookings that require department head approval
                            // Exclude bookings created by department heads (including own bookings)
                            $pendingCount = \App\Models\Booking::where('department_id', auth()->user()->department_id)
                                    ->where('status', 'pending')
                                    ->where('requested_by', '!=', auth()->id())
                                    ->whereDoesntHave('requestedBy', function($query) {
                                        $query->role('department_head');
                                    })
                                    ->count();
                        @endphp
                        <a href="{{ route('client.approvals.index') }}" 
                           class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.approvals.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Pending Approvals</span>
                                </div>
                                @if($pendingCount > 0)
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endif

                    <!-- Notifications at the end -->
                    <!-- Notifications -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('client.notifications.index') }}" 
                           class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('client.notifications.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span>Notifications</span>
                                </div>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                    $totalCount = auth()->user()->notifications()->count();
                                @endphp
                                <div class="flex items-center">
                                    @if($totalCount > 0)
                                        <form action="{{ route('client.notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')" class="mr-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800" title="Clear all notifications">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>

                @elseif(auth()->user()->hasRole('driver'))
                    <!-- Driver Sidebar -->
                    <a href="{{ route('driver.dashboard') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.dashboard') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>


                    <!-- Exit Clearance Tickets -->
                    <a href="{{ route('driver.tickets.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.tickets.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Exit Clearance
                        </div>
                    </a>

                    <a href="{{ route('driver.trips.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.trips.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            My Trips
                        </div>
                    </a>

                    <a href="{{ route('driver.schedule') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.schedule') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule
                        </div>
                    </a>

                    <!-- Feedback Section -->
                    <a href="{{ route('driver.feedback.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.feedback.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            <span>Feedback</span>
                        </div>
                    </a>

                    <!-- Vehicle Condition Reporting Section -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.vehicle-reports.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                Vehicle Reports
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" 
                                 :class="{'rotate-180': open}" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-cloak 
                             x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="mt-2 space-y-1 px-7">
                            <a href="{{ route('driver.vehicle-reports.create') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('driver.vehicle-reports.create') ? 'bg-blue-50 text-blue-600' : '' }}">
                                Report Issue
                            </a>
                            <a href="{{ route('driver.vehicle-reports.index') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('driver.vehicle-reports.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                                View Reports
                            </a>
                        </div>
                    </div>
                    
                    <!-- Notifications at the end -->
                    <!-- Notifications -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('driver.notifications.index') }}" 
                           class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('driver.notifications.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span>Notifications</span>
                                </div>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                    $totalCount = auth()->user()->notifications()->count();
                                @endphp
                                <div class="flex items-center">
                                    @if($totalCount > 0)
                                        <form action="{{ route('driver.notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')" class="mr-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800" title="Clear all notifications">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>

                @elseif(auth()->user()->hasRole('maintenance_staff'))
                    <!-- Maintenance Sidebar -->
                    <a href="{{ route('maintenance.dashboard') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.dashboard') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>


                    <a href="{{ route('maintenance.profile.edit') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.profile.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile</span>
                        </div>
                    </a>

                    <a href="{{ route('maintenance.tasks.index') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.tasks.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            My Assigned Tasks
                        </div>
                    </a>

                    <!-- Vehicle Service Requests Section -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.service-requests.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                                Vehicle Services
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" 
                                 :class="{'rotate-180': open}" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-cloak 
                             x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="mt-2 space-y-1 px-7">
                            <a href="{{ route('maintenance.service-requests.index') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('maintenance.service-requests.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                                All Service Requests
                            </a>
                            <a href="{{ route('maintenance.service-requests.create') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 rounded-lg transition duration-200 hover:bg-gray-50 {{ request()->routeIs('maintenance.service-requests.create') ? 'bg-blue-50 text-blue-600' : '' }}">
                                Schedule Service
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('maintenance.schedule') }}" 
                       class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.schedule') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule
                        </div>
                    </a>
                    
                    <!-- Notifications at the end -->
                    <!-- Notifications -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('maintenance.notifications.index') }}" 
                           class="block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('maintenance.notifications.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span>Notifications</span>
                                </div>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                    $totalCount = auth()->user()->notifications()->count();
                                @endphp
                                <div class="flex items-center">
                                    @if($totalCount > 0)
                                        <form action="{{ route('maintenance.notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')" class="mr-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800" title="Clear all notifications">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="pl-64 pt-16">
            <main class="py-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')

    <script>
        function markNotificationAsRead(id) {
            fetch(`/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Update UI to show notification is read
                    const notification = document.querySelector(`[data-notification-id="${id}"]`);
                    if (notification) {
                        notification.classList.remove('bg-blue-50');
                        const readBadge = notification.querySelector('.unread-badge');
                        if (readBadge) {
                            readBadge.remove();
                        }
                    }
                    
                    // Update unread count
                    const unreadCount = document.getElementById('notification-count');
                    if (unreadCount) {
                        const currentCount = parseInt(unreadCount.textContent);
                        if (currentCount > 0) {
                            unreadCount.textContent = currentCount - 1;
                            if (currentCount - 1 === 0) {
                                unreadCount.classList.add('hidden');
                            }
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllNotificationsAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Update UI to show all notifications are read
                    document.querySelectorAll('.notification-item').forEach(notification => {
                        notification.classList.remove('bg-blue-50');
                        const readBadge = notification.querySelector('.unread-badge');
                        if (readBadge) {
                            readBadge.remove();
                        }
                    });
                    
                    // Update unread count
                    const unreadCount = document.getElementById('notification-count');
                    if (unreadCount) {
                        unreadCount.textContent = '0';
                        unreadCount.classList.add('hidden');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
