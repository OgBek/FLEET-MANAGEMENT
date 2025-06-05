<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">My Vehicle Reports</h1>
                    </div>

                    @if($vehicleReports->isEmpty())
                        <div class="mt-6 text-center text-gray-500">
                            No vehicle reports assigned to you yet.
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported On</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($vehicleReports as $report)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($report->vehicle)
                                                    {{ $report->vehicle->formatted_brand }} {{ $report->vehicle->model }}
                                                    <div class="text-xs text-gray-500">{{ $report->vehicle->registration_number }}</div>
                                                @else
                                                    <span class="text-gray-500">Vehicle not found</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $report->title }}
                                                <div class="text-xs text-gray-500">{{ Str::limit($report->description, 50) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($report->type === 'mechanical') bg-blue-100 text-blue-800
                                                    @elseif($report->type === 'electrical') bg-yellow-100 text-yellow-800
                                                    @elseif($report->type === 'body_damage') bg-red-100 text-red-800
                                                    @elseif($report->type === 'tire') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $report->type)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($report->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($report->status === 'in_progress') bg-blue-100 text-blue-800
                                                    @elseif($report->status === 'resolved') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $report->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="{{ route('maintenance.vehicle-reports.show', $report) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $vehicleReports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
