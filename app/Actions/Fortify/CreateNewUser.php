<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Notifications\NewUserRegistered;
use App\Traits\NotifiesAdmins;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;
    use NotifiesAdmins;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'phone' => ['required', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'string', 'in:driver,maintenance_staff'],
            'license_number' => ['required_if:role,driver', 'nullable', 'string', 'max:50'],
            'specialization' => ['required_if:role,maintenance_staff', 'nullable', 'string', 'max:100'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Create the user with pending approval status
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'phone' => $input['phone'],
                'department_id' => $input['department_id'],
                'license_number' => $input['license_number'] ?? null,
                'specialization' => $input['specialization'] ?? null,
                'status' => 'inactive',
                'approval_status' => 'pending',
                'is_available' => true,
            ]);

            // Assign the selected role
            $user->assignRole($input['role']);

            // Create team for the user
            $this->createTeam($user);

            // Notify all admins about the new user registration
            $this->notifyAdmins(new NewUserRegistered($user));

            return $user;
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
