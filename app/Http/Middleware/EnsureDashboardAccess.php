<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.auth_required', false)) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
