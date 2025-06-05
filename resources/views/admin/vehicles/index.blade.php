@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex items-center justify-between">
        <h3 class="text-gray-700 text-3xl font-medium">Vehicles</h3>
        <a href="{{ route('admin.vehicles.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Add Vehicle</a>
    </div>

    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $vehicle)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    @if($vehicle->image_url)
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}" class="w-full h-48 object-cover">
                        </div>
                    @endif
                    <div class="p-4">
                        <h4 class="text-xl font-semibold text-gray-800">{{ $vehicle->registration_number }}</h4>
                        <div class="mt-2 space-y-2">
                            <p class="text-gray-600"><span class="font-medium">Plate Number:</span> {{ $vehicle->registration_number }}</p>
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
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">Edit</a>
                            <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm hover:bg-red-600">Delete</button>
                            </form>
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