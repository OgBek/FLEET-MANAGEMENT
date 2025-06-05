<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Add cache control headers -->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">

        <title>{{ config('app.name', 'Dilla University Fleet Management System') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="http://localhost/fleet/favicon.ico" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <img src="http://localhost/fleet/dilla.png" alt="Dilla University Logo" class="h-12 w-auto object-contain ml-[-10px] mr-3" style="max-width: 70px;">
                            <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name', 'Dilla University Fleet Management') }}</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if(Auth::check() && session()->has('auth.password_confirmed_at'))
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="ml-4">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                Register
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-center lg:text-left">
                            <h2 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                <span class="block">Efficient Fleet</span>
                                <span class="block text-blue-600">Management Solution</span>
                            </h2>
                            <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                Streamline your vehicle fleet operations with our comprehensive management system. Track maintenance, manage bookings, and optimize vehicle utilization all in one place.
                            </p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                @if(Auth::check() && session()->has('auth.password_confirmed_at'))
                                    <div class="rounded-md shadow">
                                        <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                                            Go to Dashboard
                                        </a>
                                    </div>
                                @else
                                    <div class="rounded-md shadow">
                                        <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                                            Get Started
                                        </a>
                                    </div>
                                    <div class="mt-3 sm:mt-0 sm:ml-3">
                                        <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 md:py-4 md:text-lg md:px-10">
                                            Sign In
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </main>
                </div>
            </div>
            <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="http://localhost/fleet/fleet.jpg" alt="Fleet of vehicles">
            </div>
        </div>

        <!-- Feature Section -->
        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Features</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Everything you need to manage your fleet
                    </p>
                </div>

                <div class="mt-10">
                    <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                        <!-- Vehicle Management -->
                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Vehicle Management</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Track vehicle details, status, and availability in real-time.
                                </p>
                            </div>
                        </div>

                        <!-- Maintenance Tracking -->
                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Maintenance Tracking</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Schedule and monitor maintenance tasks to keep your fleet in top condition.
                                </p>
                            </div>
                        </div>

                        <!-- Booking System -->
                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Booking System</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Efficient vehicle booking and scheduling system for all departments.
                                </p>
                            </div>
                        </div>

                        <!-- Reports & Analytics -->
                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Reports & Analytics</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Comprehensive reporting tools for fleet performance and utilization analysis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Cars Section -->
        <div class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center mb-12">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Available Vehicles</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Our Fleet at Your Service
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Browse our available vehicles and book your next trip with ease.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    @forelse($vehicles as $vehicle)
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="aspect-w-16 aspect-h-9">
                                <img class="w-full h-48 object-cover" src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}">
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $vehicle->model }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Available
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 space-y-2">
                                    <p><span class="font-medium text-gray-700">Registration:</span> {{ $vehicle->registration_number }}</p>
                                    <p><span class="font-medium text-gray-700">Type:</span> {{ optional($vehicle->type)->name ?? 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-700">Brand:</span> {{ optional($vehicle->brand)->name ?? 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-700">Capacity:</span> {{ $vehicle->capacity }} passengers</p>
                                </div>
                                <div class="mt-6">
                                    @auth
                                        <a href="{{ route('client.bookings.create', ['vehicle_id' => $vehicle->id]) }}" 
                                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Book Now
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" 
                                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            Login to Book
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <p class="mt-4 text-lg font-medium text-gray-900">No vehicles available at the moment</p>
                            <p class="mt-2 text-sm text-gray-500">Please check back later for available vehicles.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <div class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center mb-12">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Testimonials</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        What Our Users Say
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Read about the experiences of our satisfied users
                    </p>
                </div>

                <div class="mt-12">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        @forelse($testimonials as $testimonial)
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <div class="flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full" src="{{ $testimonial->user->profile_photo_url }}" alt="{{ $testimonial->user->name }}">
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $testimonial->user->name }}</h3>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-5 w-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <blockquote class="mt-6">
                                        <p class="text-gray-700 text-lg leading-relaxed italic">
                                            "{{ $testimonial->content }}"
                                        </p>
                                    </blockquote>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="mt-4 text-lg font-medium text-gray-900">No testimonials yet</p>
                                <p class="mt-2 text-sm text-gray-500">Be the first to share your experience!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- About Us Section -->
        <div id="about" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center mb-12">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">About Us</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Your Trusted Fleet Management Partner
                    </p>
                    <div class="mt-8 text-lg text-gray-600 max-w-3xl mx-auto">
                        <p class="mb-4">
                            We are dedicated to providing efficient and reliable fleet management solutions to Dilla University. Our system streamlines vehicle operations, maintenance, and booking processes.
                        </p>
                        <p>
                            With years of experience in the industry, we understand the challenges of fleet management and have developed a comprehensive solution that meets your needs while ensuring maximum efficiency and cost-effectiveness.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Us Section -->
        <div id="contact" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center mb-12">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Contact Us</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Get in Touch
                    </p>
                    <div class="mt-8 max-w-3xl mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="ml-3 text-gray-600">+251 972718887</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="ml-3 text-gray-600">contact@fleetmanagement.com</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="ml-3 text-gray-600">ODAYAA CAMPUS, DILLA, ETHIOPIA</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Business Hours</h3>
                                <div class="space-y-2 text-gray-600">
                                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                    <p>Saturday: 9:00 AM - 1:00 PM</p>
                                    <p>Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <span class="text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
                </div>
            </div>
        </footer>
    </body>
</html>
