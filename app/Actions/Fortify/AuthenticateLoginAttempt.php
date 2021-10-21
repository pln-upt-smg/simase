<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateLoginAttempt
{
    /**
     * @param Request $request
     * @return User|false|null
     */
    public function __invoke(Request $request): User|bool|null
    {
        $user = User::whereNip($request->nip)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    }
}
