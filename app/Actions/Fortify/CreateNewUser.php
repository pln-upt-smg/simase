<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\IsValidPhone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->whereNull('deleted_at'), new IsValidPhone],
            'nip' => ['required', 'numeric', 'digit_between:6,255', Rule::unique('users', 'nip')->whereNull('deleted_at')],
            'password' => $this->passwordRules()
        ], customAttributes: [
            'name' => 'Nama',
            'phone' => 'Nomor Telepon',
            'nip' => 'Nomor Induk Pegawai',
            'password' => 'Kata Sandi'
        ])->validate();
        return User::create([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'nip' => $input['nip'],
            'password' => Hash::make($input['password'])
        ]);
    }
}
