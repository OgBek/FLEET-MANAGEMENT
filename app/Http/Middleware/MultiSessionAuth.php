<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class MultiSessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        // Check if there's a user session for this user type
        $sessionKey = 'multi_auth_' . $userType;
        
        if (Session::has($sessionKey)) {
            $userId = Session::get($sessionKey);
            $user = User::find($userId);
            
            if ($user && $user->hasRole($userType)) {
                // Replace the current user with the requested user type
                $originalUser = Auth::user();
                $originalUserId = $originalUser ? $originalUser->id : null;
                
                // Store the original user ID in the session for restoring later
                Session::put('original_user_id', $originalUserId);
                
                // Log in as the requested user type
                Auth::login($user);
                
                // Continue with the request
                $response = $next($request);
                
                // Restore the original user after the request is complete
                if ($originalUserId) {
                    Auth::loginUsingId($originalUserId);
                } else {
                    Auth::logout();
                }
                
                return $response;
            }
        }
        
        // If no multi-auth session found for this user type, check if the current user has the role
        if (Auth::check() && Auth::user()->hasRole($userType)) {
            return $next($request);
        }
        
        // Redirect to the session selector if no valid session
        return redirect()->route('session.selector')->with('error', "No active {$userType} session found");
    }
}
