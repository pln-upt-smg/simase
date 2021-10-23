<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySecureRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->secure() && app()->isProduction() && config('app.force_https')) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}
