@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Monthly Schedule</h2>
                <div class="flex space-x-4">
                    <a href="{{ route('maintenance.schedule', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Previous Month
                    </a>
                    <span class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <a href="{{ route('maintenance.schedule', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Next Month
                    </a>
                </div>
            </div>

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

                        @if(isset($schedule[$dateStr]))
                            <div class="space-y-1">
                                @foreach($schedule[$dateStr] as $item)
                                    @if($item['type'] === 'task')
                                        <a href="{{ route('maintenance.tasks.show', $item['item']) }}" 
                                           class="block p-1 text-xs rounded bg-blue-50 text-blue-700 hover:bg-blue-100">
                                            {{ $item['item']->title }}
                                        </a>
                                    @else
                                        <a href="{{ route('maintenance.service-requests.show', $item['item']) }}" 
                                           class="block p-1 text-xs rounded bg-green-50 text-green-700 hover:bg-green-100">
                                            {{ $item['item']->issue_title }}
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
                    <span class="text-sm text-gray-600">Maintenance Tasks</span>
                </div>
                <div class="flex items-center">
                    <span class="h-4 w-4 rounded-full bg-green-100 mr-2"></span>
                    <span class="text-sm text-gray-600">Service Requests</span>
                </div>
            </div>
        </div>
    </div>
@endsection 