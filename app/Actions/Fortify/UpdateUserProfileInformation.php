<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => [
                'required',
                'string',
                'size:9',
                'regex:/^9[0-9]{8}$/',
                Rule::unique('users')->ignore($user->id)
            ],
            'photo' => [
                'nullable',
                'mimes:jpg,jpeg,png',
                'max:1024',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $image = getimagesize($value->path());
                        if (!$image) {
                            $fail('The photo must be a valid image file.');
                            return;
                        }
                        
                        // Check minimum dimensions (e.g., 100x100 pixels)
                        if ($image[0] < 100 || $image[1] < 100) {
                            $fail('The photo must be at least 100x100 pixels.');
                        }
                        
                        // Check maximum dimensions (e.g., 2000x2000 pixels)
                        if ($image[0] > 2000 || $image[1] > 2000) {
                            $fail('The photo must not exceed 2000x2000 pixels.');
                        }
                    }
                }
            ],
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Please enter your phone number.',
            'phone.size' => 'Phone number must be exactly 9 digits.',
            'phone.regex' => 'Phone number must start with 9 and be 9 digits long.',
            'phone.unique' => 'This phone number is already in use.',
            'photo.mimes' => 'Photo must be a JPG, JPEG, or PNG file.',
            'photo.max' => 'Photo size must not exceed 1MB.',
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
