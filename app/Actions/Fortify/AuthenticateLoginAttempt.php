<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateLoginAttempt
{
    /**
     * @param Request $request
     * @return User|null
     */
    public function __invoke(Request $request): ?User
    {
        $user = User::whereNip($request->nip)->first();
        return $user && Hash::check($request->password, $user->password) ? $user : null;
    }
}
