<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array $input
     * @return User
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'nip' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => $this->passwordRules()
        ])->validate();
        return User::create([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'nip' => $input['nip'],
            'password' => Hash::make($input['password'])
        ]);
    }
}
