@extends('layouts.dashboard')

@section('header')
    My Vehicles
@endsection

@section('content')
<div class="container mx-auto px-6 py-8">
    <h3 class="text-gray-700 text-3xl font-medium">Assigned Vehicles</h3>

    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $vehicle)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h4 class="text-xl font-semibold text-gray-800">{{ $vehicle->registration_number }}</h4>
                        <div class="mt-2 space-y-2">
                            <p class="text-gray-600"><span class="font-medium">Registration Number:</span> {{ $vehicle->registration_number }}</p>
                            <p class="text-gray-600"><span class="font-medium">Model:</span> {{ $vehicle->model }} ({{ $vehicle->year }})</p>
                            <p class="text-gray-600"><span class="font-medium">Type:</span> {{ optional($vehicle->type)->name ?? 'N/A' }}</p>
                            <p class="text-gray-600"><span class="font-medium">Brand:</span> {{ $vehicle->formatted_brand }}</p>
                            <p class="text-gray-600"><span class="font-medium">Category:</span> {{ optional(optional($vehicle->type)->category)->name ?? 'N/A' }}</p>
                            <p class="text-gray-600"><span class="font-medium">Capacity:</span> {{ $vehicle->capacity }} persons</p>
                            <p class="text-gray-600"><span class="font-medium">Status:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($vehicle->status === 'available') bg-green-100 text-green-800
                                    @elseif($vehicle->status === 'maintenance') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($vehicle->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('driver.vehicle-reports.create', ['vehicle' => $vehicle->id]) }}" 
                               class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600">
                                Submit Report
                            </a>
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
@endsection 