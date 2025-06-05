@extends('layouts.dashboard')

@section('header')
    My Schedule
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Monthly Schedule</h2>
                <div class="flex space-x-4">
                    <a href="{{ route('driver.schedule', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Previous Month
                    </a>
                    <span class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <a href="{{ route('driver.schedule', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Next Month
                    </a>
                </div>
            </div>

            <!-- Debug Information -->
            @php
                $hasTripsInSystem = isset($trips) && !empty($trips);
                $monthName = $currentMonth->format('F Y');
                
                // Check if there are any trips for the current month
                $tripsForCurrentMonth = false;
                if ($hasTripsInSystem) {
                    foreach ($trips as $trip) {
                        if ($trip->start_time && 
                            $trip->start_time->format('Y-m') === $currentMonth->format('Y-m')) {
                            $tripsForCurrentMonth = true;
                            break;
                        }
                    }
                }
            @endphp
            
            @if($hasTripsInSystem)
                <div class="bg-blue-50 p-4 mb-6 rounded-md border border-blue-200">
                    <h3 class="text-lg font-medium text-blue-800">Trip Information</h3>
                    <p class="mt-2 text-sm text-blue-700">Total trips: {{ $trips ? count($trips) : 0 }}</p>
                    <div class="mt-2 overflow-auto max-h-40">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr>
                                    <th class="px-2 py-1 text-left">ID</th>
                                    <th class="px-2 py-1 text-left">Status</th>
                                    <th class="px-2 py-1 text-left">Start Time</th>
                                    <th class="px-2 py-1 text-left">End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($trips) && is_iterable($trips))
                                    @foreach($trips as $trip)
                                    <tr>
                                        <td class="px-2 py-1">{{ $trip->id }}</td>
                                        <td class="px-2 py-1">{{ $trip->status }}</td>
                                        <td class="px-2 py-1">{{ $trip->start_time->format('Y-m-d H:i') }}</td>
                                        <td class="px-2 py-1">{{ $trip->end_time->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="px-2 py-2 text-center text-red-500">No trip data available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="bg-gray-50 p-2 text-center text-sm font-medium text-gray-500">
                        {{ $dayName }}
                    </div>
                @endforeach

                @php
                    $startOfMonth = $currentMonth->copy()->startOfMonth();
                    $endOfMonth = $currentMonth->copy()->endOfMonth();
                    $date = $startOfMonth->copy()->startOfWeek();
                    $endDate = $endOfMonth->copy()->endOfWeek();
                @endphp

                @while($date <= $endDate)
                    @php
                        $dateStr = $date->format('Y-m-d');
                        $isToday = $date->isToday();
                        $isCurrentMonth = $date->month === $currentMonth->month;
                    @endphp
                    
                    <div class="min-h-[120px] bg-white p-2 {{ $isCurrentMonth ? '' : 'bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm {{ $isToday ? 'font-bold text-blue-600' : ($isCurrentMonth ? 'text-gray-900' : 'text-gray-400') }}">
                                {{ $date->format('j') }}
                            </span>
                        </div>

                        @if(isset($groupedTrips) && is_array($groupedTrips) && isset($groupedTrips[$dateStr]) && !empty($groupedTrips[$dateStr]))
                            <div class="space-y-1">
                                @foreach($groupedTrips[$dateStr] as $trip)
                                    @if($trip && isset($trip->status))
                                        @php
                                            // Set color based on status
                                            $statusColor = match($trip->status) {
                                                'approved' => 'blue',
                                                'in_progress' => 'yellow',
                                                'completed' => 'green',
                                                'pending' => 'orange',
                                                'cancelled' => 'red',
                                                'rejected' => 'pink',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <a href="{{ route('driver.trips.show', $trip) }}" 
                                           class="block p-1 text-xs rounded bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 hover:bg-{{ $statusColor }}-100 border border-{{ $statusColor }}-200">
                                            <div class="flex justify-between items-center">
                                                <span>{{ $trip->start_time ? $trip->start_time->format('H:i') : 'N/A' }}</span>
                                                <span class="text-xs font-medium">
                                                    {{ ucfirst($trip->status) }}
                                                </span>
                                            </div>
                                            <div class="truncate">{{ $trip->department && isset($trip->department->name) ? $trip->department->name : 'Department' }}</div>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @php
                        $date->addDay();
                    @endphp
                @endwhile
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Legend</h3>
            <div class="flex space-x-6">
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-blue-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Upcoming</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-yellow-100 mr-2"></span>
                    <span class="text-sm text-gray-600">In Progress</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-green-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Completed</span>
                </div>
            </div>
        </div>
    </div>
@endsection 