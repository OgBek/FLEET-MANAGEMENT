 @extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_requests'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_requests'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_requests'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Urgent Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['urgent_requests'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Requests List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Service Requests</h6>
            <a href="{{ route('driver.service-requests.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('driver.service-requests.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('driver.service-requests.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Service Requests Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Completed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->vehicle->registration_number }}</td>
                            <td>{{ $request->issue_title }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status_color }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $request->priority_color }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            <td>{{ $request->completed_at ? $request->completed_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                <a href="{{ route('driver.service-requests.show', $request) }}" 
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No service requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $serviceRequests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection