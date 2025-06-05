<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Spatie\Permission\Models\Role;

class MultiSessionController extends Controller
{
    /**
     * Show the session selector page with users grouped by role.
     */
    public function showSelector()
    {
        // Get all available roles that a user can assume
        $roles = Role::whereIn('name', ['admin', 'department_head', 'department_staff', 'driver', 'maintenance_staff'])->get();
        
        // Get all active users for each role
        $usersByRole = [];
        foreach ($roles as $role) {
            $users = User::role($role->name)
                ->where('status', 'active') 
                ->where('approval_status', 'approved')
                ->get();
            
            $usersByRole[$role->name] = $users;
        }
        
        // Current active sessions
        $activeSessions = [];
        foreach ($roles as $role) {
            $sessionKey = 'multi_auth_' . $role->name;
            if (Session::has($sessionKey)) {
                $userId = Session::get($sessionKey);
                $user = User::find($userId);
                if ($user) {
                    $activeSessions[$role->name] = $user;
                }
            }
        }
        
        return view('auth.session-selector', compact('usersByRole', 'activeSessions', 'roles'));
    }
    
    /**
     * Set a user session for a specific role.
     */
    public function setSession(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string'
        ]);
        
        $user = User::findOrFail($validated['user_id']);
        
        // Verify user has the role
        if (!$user->hasRole($validated['role'])) {
            return back()->with('error', 'The selected user does not have the required role.');
        }
        
        // Set the session for this role
        $sessionKey = 'multi_auth_' . $validated['role'];
        Session::put($sessionKey, $user->id);
        
        // Update the current authentication if no user is logged in or requested
        $currentUser = Auth::user();
        if (!$currentUser) {
            Auth::login($user);
        }
        
        return back()->with('success', "Session for {$validated['role']} set to {$user->name}");
    }
    
    /**
     * Clear a user session for a specific role.
     */
    public function clearSession(Request $request)
    {
        $role = $request->input('role');
        
        if ($role) {
            $sessionKey = 'multi_auth_' . $role;
            Session::forget($sessionKey);
            return back()->with('success', "Session for {$role} has been cleared");
        }
        
        return back()->with('error', 'No role specified');
    }
    
    /**
     * Switch to an active session.
     */
    public function switchSession(Request $request)
    {
        $role = $request->input('role');
        
        if ($role) {
            $sessionKey = 'multi_auth_' . $role;
            
            if (Session::has($sessionKey)) {
                $userId = Session::get($sessionKey);
                $user = User::find($userId);
                
                if ($user) {
                    Auth::login($user);
                    
                    // Redirect to appropriate dashboard based on role
                    switch ($role) {
                        case 'admin':
                            return redirect()->route('admin.dashboard');
                        case 'department_head':
                            return redirect()->route('client.dashboard');
                        case 'department_staff':
                            return redirect()->route('client.dashboard');
                        case 'driver':
                            return redirect()->route('driver.dashboard');
                        case 'maintenance_staff':
                            return redirect()->route('maintenance.dashboard');
                        default:
                            return redirect()->route('welcome');
                    }
                }
            }
            
            return back()->with('error', "No active session for {$role}");
        }
        
        return back()->with('error', 'No role specified');
    }
}
