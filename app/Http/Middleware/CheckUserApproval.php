<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproval
{
    protected $except = [
        'login',
        'register',
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'verification.notice',
        'verification.verify',
        'verification.send'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for excluded routes
        if ($this->shouldSkipMiddleware($request)) {
            return $next($request);
        }

        // Skip middleware if user is not logged in
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Always allow admin access
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Department staff don't need approval
        if ($user->hasRole('department_staff')) {
            return $next($request);
        }

        // Check approval for other users
        if (!$user->isApproved()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is pending approval. Please wait for admin approval.']);
        }

        return $next($request);
    }

    protected function shouldSkipMiddleware($request): bool
    {
        return in_array($request->route()->getName(), $this->except);
    }
} 