<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department']);

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by account status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(4);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        // If role is driver, auto-assign to Driver Department
        if ($request->role === 'driver') {
            $driverDepartment = Department::where('name', 'like', '%driver%')->first();
            if ($driverDepartment) {
                $request->merge(['department_id' => $driverDepartment->id]);
            }
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'phone' => [
                'required', 
                'string', 
                'regex:/^9[0-9]{8}$/',
                'unique:users,phone'
            ],
            'license_number' => [
                'nullable',
                'string',
                'min:5',
                'max:15',
                'regex:/^[A-Za-z0-9]{5,15}$/i',
                Rule::requiredIf(fn() => $request->role === 'driver'),
                Rule::when($request->role === 'driver', ['unique:users,license_number'])
            ],
            'specialization' => 'nullable|string|max:100',
        ], [
            'phone.unique' => 'This phone number is already in use by another user.',
            'phone.regex' => 'The phone number must start with 9 and be 9 digits long.',
            'license_number.required_if' => 'License number is required for drivers.',
            'license_number.unique' => 'This driver license number is already registered. Each driver must have a unique license number.'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'],
            'license_number' => $validated['license_number'],
            'specialization' => $validated['specialization'],
            'approval_status' => 'approved', // Admin-created users are auto-approved
            'status' => 'active'
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'department', 'bookingsRequested', 'bookingsAssigned']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Get all role names for validation
        $roleNames = Role::pluck('name')->toArray();
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^9[0-9]{8}$/i',
                Rule::unique('users')->ignore($user->id),
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => [
                'required',
                'string',
                Rule::in($roleNames),
            ],
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
            'approval_status' => [
                'required',
                Rule::in(['pending', 'approved', 'rejected']),
            ],
            'license_number' => [
                Rule::requiredIf(fn() => $request->role === 'driver'),
                'nullable',
                'string',
                'min:5',
                'max:15',
                'regex:/^[A-Za-z0-9]{5,15}$/i',
                Rule::unique('users')->ignore($user->id),
            ],
            'specialization' => [
                Rule::requiredIf(fn() => in_array($request->role, ['mechanic', 'maintenance_staff'])),
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Please enter the user\'s name.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address (e.g., example@example.com).',
            'email.unique' => 'This email address is already in use.',
            'phone.required' => 'Please enter a phone number.',
            'phone.regex' => 'Phone number must start with 9 and be 9 digits long (e.g., 912345678).',
            'phone.unique' => 'This phone number is already in use.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Selected role is not valid. Valid roles are: ' . implode(', ', $roleNames),
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department does not exist.',
            'license_number.required_if' => 'License number is required for drivers.',
            'license_number.regex' => 'License number must be 5-15 characters and can only contain letters and numbers.',
            'license_number.unique' => 'This driver license number is already registered. Each driver must have a unique license number.',
            'license_number.min' => 'License number must be at least 5 characters.',
            'license_number.max' => 'License number cannot exceed 15 characters.',
            'specialization.required' => 'Specialization is required for this role.',
            'specialization.regex' => 'Specialization can only contain letters, spaces, and hyphens.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Update user
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'], // Store without country code
            'department_id' => $validated['department_id'],
            'status' => $validated['status'],
            'approval_status' => $validated['approval_status'],
        ];
        
        // Only update these fields if they exist in the request
        if (isset($validated['license_number'])) {
            $userData['license_number'] = $validated['license_number'];
        }
        
        if (isset($validated['specialization'])) {
            $userData['specialization'] = $validated['specialization'];
        }
        
        $user->update($userData);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update role if it has changed
        if (!$user->hasRole($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has any related records
        if ($user->bookingsRequested()->exists() || 
            $user->bookingsAssigned()->exists() || 
            $user->maintenanceRecords()->exists()) {
            return back()->with('error', 'This user has related records and cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    public function approve(User $user)
    {
        if (!$user->isPending()) {
            return back()->with('error', 'This user is not pending approval.');
        }

        $user->approve();

        return back()->with('status', 'User approved successfully.');
    }

    public function reject(User $user)
    {
        if (!$user->isPending()) {
            return back()->with('error', 'This user is not pending approval.');
        }

        $user->reject();

        return back()->with('status', 'User rejected successfully.');
    }
}
