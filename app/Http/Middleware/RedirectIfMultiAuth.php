<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfMultiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard = null): Response
    {
        // If a specific guard is requested
        if ($request->has('guard') && in_array($request->guard, ['admin', 'department_head', 'department_staff', 'driver', 'maintenance_staff'])) {
            $guard = $request->guard;
        }
        
        // If guard is specified, check that guard
        if ($guard && Auth::guard($guard)->check()) {
            return $next($request);
        }
        
        // Check all role-specific guards if no specific guard is provided
        if (!$guard) {
            foreach (['admin', 'department_head', 'department_staff', 'driver', 'maintenance_staff'] as $roleGuard) {
                if (Auth::guard($roleGuard)->check()) {
                    return $next($request);
                }
            }
        }
        
        // Fall back to web guard if no role-specific guards are authenticated
        if (Auth::guard('web')->check()) {
            return $next($request);
        }
        
        // Redirect to login if no guards are authenticated
        return redirect()->route('login');
    }
}
