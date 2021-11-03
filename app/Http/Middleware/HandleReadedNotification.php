<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleReadedNotification
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
        if (!is_null(auth()->user())) {
            $readed = (string)$request->headers->get('Readed-Notification', false);
            if ($readed === '1') {
                auth()->user()->unreadNotifications->markAsRead();
            }
            $request->headers->remove('Readed-Notification');
        }
        return $next($request);
    }
}
