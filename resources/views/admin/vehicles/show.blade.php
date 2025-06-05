@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <!-- Header -->
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Vehicle Details</h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Edit Vehicle
                        </a>
                        <a href="{{ route('admin.vehicles.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="px-4 py-5 sm:p-6">
                <!-- Vehicle Image -->
                @if($vehicle->image_url)
                    <div class="mb-6">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Vehicle Image</dt>
                        <dd class="mt-1">
                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->registration_number }}" class="h-48 w-48 object-cover rounded-lg shadow-sm">
                        </dd>
                    </div>
                @endif

                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->registration_number }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $vehicle->status === 'available' ? 'bg-green-100 text-green-800' : 
                                   ($vehicle->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800') }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->formatted_brand }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->model }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->type->category->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Color</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->color }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Year</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->year }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">VIN Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->vin_number }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Engine Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->engine_number }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($vehicle->current_mileage) }} km</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fuel Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($vehicle->fuel_type) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Insurance Expiry</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $vehicle->insurance_expiry ? $vehicle->insurance_expiry->format('M d, Y') : 'Not set' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Maintenance</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $vehicle->last_maintenance_date ? $vehicle->last_maintenance_date->format('M d, Y') : 'No maintenance record' }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vehicle->notes ?: 'No notes available' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Recent Bookings -->
            <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h4>
                @if($vehicle->bookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($vehicle->bookings->take(5) as $booking)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $booking->requestedBy->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $booking->start_time ? $booking->start_time->format('M d, Y H:i') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $booking->end_time ? $booking->end_time->format('M d, Y H:i') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $booking->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No bookings found for this vehicle.</p>
                @endif
            </div>

            <!-- Maintenance History -->
            <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Maintenance History</h4>
                @if($vehicle->maintenanceRecords->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($vehicle->maintenanceRecords->take(5) as $maintenance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $maintenance->service_date ? $maintenance->service_date->format('M d, Y') : 'Not completed' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $maintenance->type }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $maintenance->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($maintenance->cost, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No maintenance records found for this vehicle.</p>
                @endif
            </div>

            <!-- Delete Vehicle -->
            <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
                <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" class="flex justify-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')">
                        Delete Vehicle
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection 