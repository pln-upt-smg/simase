<?php

namespace App\Http\Middleware\Authorizable;

use Closure;
use Illuminate\Http\Request;
use Throwable;

class ByOperator
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
        $user = auth()->user()?->load('role');
        if (is_null($user)) {
            return redirect('login');
        }
        if (!$user->role->isAdministrator() && !$user->role->isOperator()) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}
