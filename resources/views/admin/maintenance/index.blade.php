@extends('layouts.dashboard')

@section('content')
    <script>
        // Redirect to maintenance schedules page
        window.location.href = "{{ route('admin.maintenance-schedules.index') }}";
    </script>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-medium">Redirecting to Maintenance Schedules...</h2>
            <p>If you are not redirected automatically, please <a href="{{ route('admin.maintenance-schedules.index') }}" class="text-blue-600 hover:underline">click here</a>.</p>
        </div>
    </div>
@endsection 