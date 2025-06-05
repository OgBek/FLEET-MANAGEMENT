@extends('layouts.dashboard')

@section('header')
    Vehicle Inspection Checklist
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('driver.inspections.store') }}" method="POST">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <input type="hidden" name="vehicle_id" value="{{ $trip->vehicle->id }}">
                <input type="hidden" name="inspection_type" value="{{ $type }}">

                <!-- Vehicle Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $trip->vehicle->registration_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $trip->vehicle->brand->name }} {{ $trip->vehicle->model }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Current Mileage</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($trip->vehicle->current_mileage) }} km</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Inspection Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($type) }} Trip Inspection</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Exterior Inspection -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Exterior Inspection</h3>
                    <div class="space-y-4">
                        @foreach(['Body Condition', 'Lights', 'Mirrors', 'Windows', 'Tires', 'License Plates', 'Fluid Leaks'] as $item)
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-700">{{ $item }}</label>
                                </div>
                                <div class="ml-4 flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="radio" name="exterior_{{ Str::snake($item) }}" value="pass" required
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Pass</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="exterior_{{ Str::snake($item) }}" value="fail" required
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Fail</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Interior Inspection -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Interior Inspection</h3>
                    <div class="space-y-4">
                        @foreach(['Seats & Belts', 'Dashboard Lights', 'Horn', 'Wipers', 'AC/Heater', 'Brakes', 'Steering'] as $item)
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-700">{{ $item }}</label>
                                </div>
                                <div class="ml-4 flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="radio" name="interior_{{ Str::snake($item) }}" value="pass" required
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Pass</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="interior_{{ Str::snake($item) }}" value="fail" required
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Fail</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Safety Equipment -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Safety Equipment</h3>
                    <div class="space-y-4">
                        @foreach(['First Aid Kit', 'Fire Extinguisher', 'Warning Triangle', 'Spare Tire', 'Jack & Tools'] as $item)
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-700">{{ $item }}</label>
                                </div>
                                <div class="ml-4 flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="radio" name="safety_{{ Str::snake($item) }}" value="pass" required
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Pass</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="safety_{{ Str::snake($item) }}" value="fail" required
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Fail</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Fluid Levels -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Fluid Levels</h3>
                    <div class="space-y-4">
                        @foreach(['Engine Oil', 'Coolant', 'Brake Fluid', 'Power Steering', 'Windshield Washer'] as $item)
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-700">{{ $item }}</label>
                                </div>
                                <div class="ml-4 flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="radio" name="fluid_{{ Str::snake($item) }}" value="pass" required
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Pass</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="fluid_{{ Str::snake($item) }}" value="fail" required
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300">
                                        <label class="ml-2 text-sm text-gray-700">Fail</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Notes</h3>
                    <div>
                        <label for="notes" class="sr-only">Notes</label>
                        <textarea id="notes" name="notes" rows="4"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Enter any additional notes or observations about the vehicle's condition..."></textarea>
                    </div>
                </div>

                <!-- Odometer Reading -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Odometer Reading</h3>
                    <div>
                        <label for="odometer" class="block text-sm font-medium text-gray-700">Current Reading (km)</label>
                        <input type="number" id="odometer" name="odometer" required
                               min="{{ $trip->vehicle->current_mileage }}"
                               value="{{ $trip->vehicle->current_mileage }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('driver.trips.show', $trip) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Inspection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 