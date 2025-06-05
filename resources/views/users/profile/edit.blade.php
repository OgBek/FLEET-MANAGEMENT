@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-gray-700 text-3xl font-medium">My Profile</h3>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            @include('profile.partials.status-messages')
            
            <x-profile-form :user="$user" :route="route('profile.update')" />
        </div>
    </div>
</div>
@endsection
