@extends('layouts.dashboard')

@section('header')
    Available Vehicles
@endsection

@section('content')
<div class="container mx-auto px-6 py-8">
    <h3 class="text-gray-700 text-3xl font-medium">Available Vehicles</h3>

    <!-- Toast Container - Fixed position at the top right -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 flex flex-col gap-4 w-80 max-w-full"></div>

    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $vehicle)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    @if($vehicle->image_url)
                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-4">
                        <h4 class="text-xl font-semibold text-gray-800">{{ $vehicle->registration_number }}</h4>
                        <div class="mt-2 space-y-2">
                            <p class="text-gray-600"><span class="font-medium">Registration Number:</span> {{ $vehicle->registration_number }}</p>
                            <p class="text-gray-600"><span class="font-medium">Model:</span> {{ $vehicle->model }} ({{ $vehicle->year }})</p>
                            <p class="text-gray-600"><span class="font-medium">Type:</span> {{ optional($vehicle->type)->name ?? 'N/A' }}</p>
                            <p class="text-gray-600"><span class="font-medium">Brand:</span> {{ $vehicle->formatted_brand }}</p>
                            <p class="text-gray-600"><span class="font-medium">Category:</span> {{ optional(optional($vehicle->type)->category)->name ?? 'N/A' }}</p>
                            <p class="text-gray-600"><span class="font-medium">Capacity:</span> {{ $vehicle->capacity ?? 'N/A' }} persons</p>
                            <p class="text-gray-600"><span class="font-medium">Status:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($vehicle->status === 'available') bg-green-100 text-green-800
                                    @elseif($vehicle->status === 'maintenance') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($vehicle->status ?? 'unknown') }}
                                </span>
                            </p>
                        </div>
                        <div class="mt-4 flex justify-end space-x-3">
                            <a href="{{ route('client.vehicles.show', $vehicle) }}" 
                               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-200">
                                View Details
                            </a>
                            @if($vehicle->status === 'available')
                                <a href="{{ route('client.bookings.create', ['vehicle' => $vehicle->id]) }}" 
                                   class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600">
                                    Book Now
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $vehicles->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toast notification system
    function showToast(message, type = 'success', duration = 5000) {
        const container = document.getElementById('toast-container');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `animate-slide-in-right flex items-center p-4 mb-4 rounded-lg shadow transition-opacity ${type === 'success' ? 'bg-green-50 text-green-800 border-l-4 border-green-500' : 'bg-red-50 text-red-800 border-l-4 border-red-500'}`;
        toast.role = 'alert';
        
        // Icon based on type
        const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        iconSvg.setAttribute('class', `w-5 h-5 ${type === 'success' ? 'text-green-500' : 'text-red-500'}`);
        iconSvg.setAttribute('aria-hidden', 'true');
        iconSvg.setAttribute('fill', 'currentColor');
        iconSvg.setAttribute('viewBox', '0 0 20 20');
        
        const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        if (type === 'success') {
            iconPath.setAttribute('d', 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z');
        } else {
            iconPath.setAttribute('d', 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z');
        }
        iconSvg.appendChild(iconPath);
        
        // Message text
        const text = document.createElement('div');
        text.className = 'ml-3 text-sm font-medium';
        text.textContent = message;
        
        // Close button
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = `ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:bg-green-100' : 'bg-red-50 text-red-500 hover:bg-red-100'} rounded-lg focus:ring-2 p-1.5 inline-flex h-8 w-8`;
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.onclick = () => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        };
        
        const closeIconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        closeIconSvg.setAttribute('class', 'w-5 h-5');
        closeIconSvg.setAttribute('aria-hidden', 'true');
        closeIconSvg.setAttribute('fill', 'currentColor');
        closeIconSvg.setAttribute('viewBox', '0 0 20 20');
        
        const closePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        closePath.setAttribute('fill-rule', 'evenodd');
        closePath.setAttribute('d', 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z');
        closePath.setAttribute('clip-rule', 'evenodd');
        closeIconSvg.appendChild(closePath);
        closeButton.appendChild(closeIconSvg);
        
        // Assemble toast
        toast.appendChild(iconSvg);
        toast.appendChild(text);
        toast.appendChild(closeButton);
        
        // Add to container
        container.appendChild(toast);
        
        // Auto-remove after duration
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    }
    
    // Check for session messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        
        // Check for URL query parameters that indicate booking errors
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('booking_error')) {
            const errorMsg = urlParams.get('booking_error');
            showToast(decodeURIComponent(errorMsg), 'error');
        }
    });
</script>

<style>
    /* Animation for toast notifications */
    .animate-slide-in-right {
        animation: slideInRight 0.3s ease-out forwards;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Transition for fade out */
    .transition-opacity {
        transition: opacity 0.3s ease-out;
    }
</style>
@endpush

@endsection