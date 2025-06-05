<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Rate limiting: Allow 5 attempts per minute
        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . $seconds . ' seconds.'],
            ]);
        }

        // Validate the request
        $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8'
            ]
        ], [
            'email.regex' => 'Please enter a valid email address.',
            'password.min' => 'Password must be at least 8 characters.'
        ]);

        // Clear any existing sessions
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Attempt to authenticate
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            RateLimiter::clear($key); // Clear rate limiting on successful login
            
            $user = Auth::user();
            
            // Check if user is approved (except for admin)
            if (!$user->hasRole('admin') && !$user->isApproved()) {
                Auth::logout();
                return back()
                    ->withInput(['email' => $request->email])
                    ->withErrors(['email' => 'Your account is pending approval.']);
            }

            // Check if user is active
            if (!$user->hasRole('admin') && $user->status !== 'active') {
                Auth::logout();
                return back()
                    ->withInput(['email' => $request->email])
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }

            $request->session()->regenerate();

            // Store user role in session
            $request->session()->put('user_role', $user->roles->first()->name ?? 'user');

            // Redirect based on role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasAnyRole(['department_head', 'department_staff'])) {
                return redirect()->route('client.dashboard');
            } elseif ($user->hasRole('driver')) {
                return redirect()->route('driver.dashboard');
            } elseif ($user->hasRole('maintenance_staff')) {
                return redirect()->route('maintenance.dashboard');
            }

            return redirect()->route('dashboard');
        }

        // On failed login attempt
        RateLimiter::hit($key, 60); // Remember the attempt for 60 seconds

        return back()
            ->withInput(['email' => $request->email])
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
