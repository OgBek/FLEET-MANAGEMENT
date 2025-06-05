@extends('layouts.dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Reports Overview -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Reports</h2>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Bookings Report -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bookings Report</h3>
                    <p class="text-sm text-gray-500 mb-4">View detailed reports about vehicle bookings, including usage patterns and department statistics.</p>
                    <a href="{{ route('admin.reports.bookings') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Report
                    </a>
                </div>

                <!-- Maintenance Report -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Report</h3>
                    <p class="text-sm text-gray-500 mb-4">Track maintenance costs, schedules, and service history for all vehicles.</p>
                    <a href="{{ route('admin.reports.maintenance') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Report
                    </a>
                </div>

                <!-- Vehicle Report -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Report</h3>
                    <p class="text-sm text-gray-500 mb-4">Get comprehensive reports about vehicle inventory, status, and performance.</p>
                    <a href="{{ route('admin.reports.vehicles') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Report
                    </a>
                </div>
                
                <!-- Departments Report -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Departments Report</h3>
                    <p class="text-sm text-gray-500 mb-4">Analyze department activity, including booking statistics, user counts, and resource usage.</p>
                    <a href="{{ route('admin.reports.departments') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Report
                    </a>
                </div>
                
                <!-- Users Report -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Users Report</h3>
                    <p class="text-sm text-gray-500 mb-4">View user statistics, including booking history, department, role, and activity metrics.</p>
                    <a href="{{ route('admin.reports.users') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="py-6 px-4 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Export Reports</h2>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <p class="text-sm text-gray-500">Export reports in various formats for offline analysis and record keeping.</p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('admin.reports.export', 'bookings') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Export Bookings
                        </a>
                        <a href="{{ route('admin.reports.export', 'vehicles') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Export Vehicles
                        </a>
                        <a href="{{ route('admin.reports.export', 'maintenance') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Export Maintenance
                        </a>
                        <a href="{{ route('admin.reports.export', 'departments') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Export Departments
                        </a>
                        <a href="{{ route('admin.reports.export', 'users') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Export Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection