<?php

namespace App\Actions\Fortify;

use App\Rules\IsValidPhone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     * @throws ValidationException
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id), new IsValidPhone],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ], customAttributes: [
            'name' => 'Nama Pegawai',
            'phone' => 'Nomor Telepon',
            'photo' => 'Foto Profil'
        ])->validateWithBag('updateProfileInformation');
        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }
        $user->forceFill([
            'name' => $input['name'],
            'phone' => $input['phone']
        ])->save();
    }
}
