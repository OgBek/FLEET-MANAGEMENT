<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MultiAuthController extends Controller
{
    /**
     * Show the multi-auth login form
     */
    public function showLoginForm()
    {
        // Get all available roles
        $roles = Role::whereIn('name', ['admin', 'department_head', 'department_staff', 'driver', 'maintenance_staff'])->get();
        
        // Check which guard/roles are currently authenticated
        $activeGuards = [];
        foreach ($roles as $role) {
            if (Auth::guard($role->name)->check()) {
                $activeGuards[$role->name] = Auth::guard($role->name)->user();
            }
        }
        
        return view('auth.multi-login', compact('roles', 'activeGuards'));
    }
    
    /**
     * Handle login request for role-specific guard
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'guard' => 'required|string',
        ]);
        
        // Verify guard is valid
        if (!in_array($validated['guard'], ['admin', 'department_head', 'department_staff', 'driver', 'maintenance_staff'])) {
            return back()->with('error', 'Invalid user type selected.');
        }
        
        // Attempt to log in the user with role-specific guard
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password']
        ];
        
        // Find the user
        $user = User::where('email', $validated['email'])->first();
        
        // Check if user exists and has the required role
        if (!$user || !$user->hasRole($validated['guard'])) {
            return back()->with('error', 'The selected user does not have the required role.');
        }
        
        // Attempt to authenticate the user
        if (Auth::guard($validated['guard'])->attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirect to appropriate dashboard based on role
            return $this->redirectToDashboard($validated['guard']);
        }
        
        return back()->with('error', 'Invalid credentials for selected user type.');
    }
    
    /**
     * Logout from a specific guard
     */
    public function logout(Request $request, $guard)
    {
        Auth::guard($guard)->logout();
        
        return back()->with('success', "Logged out from {$guard} session.");
    }
    
    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard($guard)
    {
        switch ($guard) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'department_head':
            case 'department_staff':
                return redirect()->route('client.dashboard');
            case 'driver':
                return redirect()->route('driver.dashboard');
            case 'maintenance_staff':
                return redirect()->route('maintenance.dashboard');
            default:
                return redirect('/');
        }
    }
}
