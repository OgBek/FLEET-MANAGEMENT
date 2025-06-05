<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Determine which view to use based on user role
        $view = 'profile.edit'; // default view
        if ($user->hasRole('admin')) {
            $view = 'admin.profile.edit';
        } elseif ($user->hasRole('driver')) {
            $view = 'driver.profile.edit';
        } elseif ($user->hasRole('maintenance_staff')) {
            $view = 'maintenance.profile.edit';
        }
        
        return view($view, [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if delete_photo is set to 1 and handle photo deletion only
        if ($request->input('delete_photo') == '1') {
            $user->deleteProfilePhoto();
            return back()->with('status', 'photo-deleted');
        }
        
        // Check if only updating photo
        $isOnlyPhotoUpdate = $request->hasFile('photo') && 
                            !$request->filled('name') && 
                            !$request->filled('email') && 
                            !$request->filled('phone') && 
                            !$request->filled('password');
        
        if ($isOnlyPhotoUpdate) {
            $validated = $request->validate([
                'photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png',
                    'max:1024',
                    'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
                ],
            ], [
                'photo.image' => 'The uploaded file must be an image.',
                'photo.mimes' => 'The image must be a JPG, JPEG, or PNG file.',
                'photo.max' => 'The image must not be larger than 1MB.',
                'photo.dimensions' => 'Image dimensions must be between 100x100 and 2000x2000 pixels.',
            ]);
            
            $user->updateProfilePhoto($request->file('photo'));
            return back()->with('status', 'profile-updated');
        }
        
        // Phone validation - custom approach to prevent self-validation issues
        $phoneValidation = ['required', 'string', 'regex:/^9[0-9]{8}$/'];
        
        // Check if the phone number is already in use by ANOTHER user
        if ($request->filled('phone') && $request->phone !== $user->phone) {
            $existingUser = \App\Models\User::where('phone', $request->phone)
                ->where('id', '!=', $user->id)
                ->first();
                
            if ($existingUser) {
                return back()->withErrors([
                    'phone' => 'This phone number is already registered. Please use a different number.'
                ])->withInput();
            }
        }
        
        // Full profile update
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
                'email:rfc',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => $phoneValidation,
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:1024',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            'current_password' => [
                'required_with:password',
                'current_password',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/',
            ],
        ], [
            'name.required' => 'Please enter your name.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address (e.g., example@example.com).',
            'email.unique' => 'This email address is already in use.',
            'phone.required' => 'Please enter a phone number.',
            'phone.regex' => 'Please enter a valid Ethiopian phone number starting with 9 followed by 8 digits.',
            'phone.unique' => 'This phone number is already registered. Please use a different number.',
            'photo.image' => 'The uploaded file must be an image.',
            'photo.mimes' => 'The image must be a JPG, JPEG, or PNG file.',
            'photo.max' => 'The image must not be larger than 1MB.',
            'photo.dimensions' => 'Image dimensions must be between 100x100 and 2000x2000 pixels.',
            'current_password.required_with' => 'Please enter your current password.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain letters, numbers, and symbols.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Update basic information
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
        }

        // Update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's profile photo.
     */
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->deleteProfilePhoto();

        // Determine redirect route based on user role
        $route = 'client.profile.edit'; // default route
        if ($user->hasRole('admin')) {
            $route = 'admin.profile.edit';
        } elseif ($user->hasRole('driver')) {
            $route = 'driver.profile.edit';
        } elseif ($user->hasRole('maintenance_staff')) {
            $route = 'maintenance.profile.edit';
        }

        return Redirect::route($route)->with('status', 'photo-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}