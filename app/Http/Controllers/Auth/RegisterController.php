<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use App\Traits\NotifiesAdmins;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use NotifiesAdmins;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $roles = Role::whereNotIn('name', ['admin'])->get();
        $departments = Department::all();
        return view('auth.register', compact('roles', 'departments'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^9[0-9]{8}$/',
                'unique:users,phone'
            ],
            'role_id' => [
                'required',
                'exists:roles,id'
            ],
            'department_id' => [
                'required',
                'exists:departments,id'
            ],
            'license_number' => [
                'nullable',
                'required_if:role_id,driver',
                'string',
                'min:5',
                'max:15',
                'regex:/^[A-Za-z0-9]{5,15}$/i',
                'unique:users,license_number'
            ],
            'specialization' => [
                'nullable',
                'required_if:role_id,maintenance_staff',
                'string',
                'max:100'
            ],
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'phone.regex' => 'Please enter a valid Ethiopian phone number starting with 9 followed by 8 digits.',
            'phone.unique' => 'This phone number is already registered. Please use a different number or sign in.',
            'license_number.regex' => 'License number must be 5-15 characters and can only contain letters and numbers.',
            'license_number.required_if' => 'License number is required for drivers.',
            'license_number.unique' => 'This driver license number is already registered. Each driver must have a unique license number.',
            'specialization.required_if' => 'Specialization is required for maintenance staff.'
        ]);

        // Additional validation for department based on role
        $validator->after(function ($validator) use ($request) {
            $role = Role::find($request->role_id);
            $department = Department::find($request->department_id);
            
            if ($role && $department) {
                $roleName = strtolower($role->name);
                $departmentName = strtolower($department->name);

                if ($roleName === 'driver' && $departmentName !== 'driver') {
                    $validator->errors()->add('department_id', 'Drivers can only be assigned to the Driver department.');
                } elseif ($roleName === 'maintenance_staff' && $departmentName !== 'maintenance') {
                    $validator->errors()->add('department_id', 'Maintenance staff can only be assigned to the Maintenance department.');
                } elseif ($roleName === 'department_staff' && ($departmentName === 'driver' || $departmentName === 'maintenance')) {
                    $validator->errors()->add('department_id', 'Department staff cannot be assigned to Driver or Maintenance departments.');
                } elseif ($roleName === 'department_head') {
                    if ($department->hasDepartmentHead()) {
                        $validator->errors()->add('role_id', 'This department already has a department head.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Format phone number to include country code
        $phone = '+251' . $request->phone;

        // Determine approval status based on role
        $role = Role::find($request->role_id);
        $approvalStatus = 'pending';
        $status = 'inactive';
        
        // Department staff don't need approval
        if ($role->name === 'department_staff') {
            $approvalStatus = 'approved';
            $status = 'active';
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $phone,
            'department_id' => $request->department_id,
            'license_number' => $request->license_number,
            'specialization' => $request->specialization,
            'approval_status' => $approvalStatus,
            'status' => $status
        ]);

        // Assign the role
        $user->assignRole($role->name);

        // Fire registered event and notify admins about the new registration
        event(new Registered($user));
        $this->notifyAdmins(new NewUserRegistered($user));

        // Redirect to security questions setup
        return redirect()->route('security-questions.setup', ['userId' => $user->id])
            ->with('success', 'Registration successful! Please set up your security questions for account recovery.');
    }

    protected function getRedirectBasedOnRole($user)
    {
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        } elseif ($user->hasRole('department_head') || $user->hasRole('department_staff')) {
            return route('client.dashboard');
        } elseif ($user->hasRole('driver')) {
            return route('driver.dashboard');
        } elseif ($user->hasRole('maintenance_staff')) {
            return route('maintenance.dashboard');
        }

        return route('dashboard');
    }
}
