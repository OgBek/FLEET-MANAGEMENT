@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Multi-User Session Manager
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Use this panel to login with different user roles simultaneously for testing purposes.
                </p>
            </div>

            <!-- Show alerts -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="px-4 py-5 sm:p-6">
                <!-- Active sessions -->
                <div class="mb-8">
                    <h4 class="text-base font-medium text-gray-900 mb-4">Active Sessions</h4>
                    
                    @if(count($activeSessions) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            @foreach($activeSessions as $role => $user)
                                <div class="flex items-center p-4 border rounded-md bg-blue-50">
                                    <div class="flex-1">
                                        <div class="font-medium">{{ ucfirst(str_replace('_', ' ', $role)) }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->name }} ({{ $user->email }})</div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <form action="{{ route('session.switch') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $role }}">
                                            <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition ease-in-out duration-150">
                                                Switch
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('session.clear') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $role }}">
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:border-gray-300 focus:shadow-outline-gray active:bg-gray-200 transition ease-in-out duration-150">
                                                Clear
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-md bg-yellow-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">No active sessions</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Select a user from the lists below to start a session.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Available users by role -->
                @foreach($roles as $role)
                    @php $roleUsers = $usersByRole[$role->name] ?? collect(); @endphp
                    
                    <div class="mb-8">
                        <h4 class="text-base font-medium text-gray-900 mb-3">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }} Users
                        </h4>
                        
                        @if($roleUsers->count() > 0)
                            <form action="{{ route('session.set') }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role->name }}">
                                
                                <div class="flex">
                                    <select name="user_id" class="flex-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        @foreach($roleUsers as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Set Session
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-sm text-gray-500 mb-2">No users found with this role.</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
                <a href="{{ route('welcome') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
