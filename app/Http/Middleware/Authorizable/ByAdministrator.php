<?php

namespace App\Http\Middleware\Authorizable;

use Closure;
use Illuminate\Http\Request;
use Throwable;

class ByAdministrator
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();
        if (is_null($user)) {
            return redirect('login');
        }
        $user = $user->load('role');
        if (!is_null($user->role) && !$user->role->isAdministrator()) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}
