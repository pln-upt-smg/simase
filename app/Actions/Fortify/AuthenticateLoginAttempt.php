<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateLoginAttempt
{
    public function __invoke(Request $request)
    {
        $user = User::whereNip($request->nip)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    }
}
