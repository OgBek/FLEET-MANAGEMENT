@extends('layouts.dashboard')

@section('content')
    <div class="bg-white shadow-sm rounded-lg">
        <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Vehicle Report</h2>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200">
            <form action="{{ route('admin.reports.vehicles') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>In Maintenance</option>
                        <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Filter Report
                    </button>
                    @if(count($vehicles) > 0)
                        <a href="{{ route('admin.reports.export', ['type' => 'vehicles']) }}" class="ml-2 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Export to CSV
                        </a>
                        <a href="{{ route('admin.reports.export', ['type' => 'vehicles', 'format' => 'pdf']) }}" class="ml-2 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Export to PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Report Content -->
        <div class="p-4">
            @if(count($vehicles) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vehicle Details
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category/Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bookings
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Maintenance
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($vehicles as $data)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $data['vehicle']->brand->name }} {{ $data['vehicle']->model }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $data['vehicle']->registration_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $data['category'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $data['type'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $data['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                                               ($data['status'] === 'maintenance' ? 'bg-red-100 text-red-800' : 
                                                'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($data['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $data['total_bookings'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            Total: {{ $data['total_maintenance'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Last: {{ $data['last_maintenance'] ?? 'Never' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">No vehicles found matching the selected criteria.</p>
                </div>
            @endif
        </div>
    </div>
@endsection 