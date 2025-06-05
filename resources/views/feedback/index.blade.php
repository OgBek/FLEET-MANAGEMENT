@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">My Feedback</h2>
                    <a href="{{ route('feedback.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Share Feedback
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse($feedbacks as $feedback)
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ ucfirst($feedback->type) }} Feedback
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $feedback->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $feedback->is_approved ? 'Approved' : 'Pending Approval' }}
                                        </span>
                                        @if($feedback->is_public)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Public
                                            </span>
                                        @endif
                                    </div>
                                    @if($feedback->booking)
                                        <p class="mt-1 text-sm text-gray-500">
                                            Booking #{{ $feedback->booking->id }} - {{ $feedback->booking->vehicle->registration_number }}
                                        </p>
                                    @endif
                                    <div class="mt-2 flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-5 w-5 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                 fill="currentColor" 
                                                 viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <p class="mt-2 text-sm text-gray-700">{{ $feedback->content }}</p>
                                    <p class="mt-2 text-xs text-gray-500">Submitted {{ $feedback->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('feedback.edit', $feedback) }}" 
                                       class="text-blue-600 hover:text-blue-800">Edit</a>
                                    <form action="{{ route('feedback.destroy', $feedback) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this feedback?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No feedback yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Share your experience with our services.</p>
                            <div class="mt-6">
                                <a href="{{ route('feedback.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Share Your First Feedback
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($feedbacks->hasPages())
                    <div class="mt-6">
                        {{ $feedbacks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
