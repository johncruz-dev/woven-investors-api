<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! config('security.auth_required', false)) {
            return $next($request);
        }

        if ($request->attributes->get('authenticated_via_api_key')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            return $this->deny($request, 401, 'Unauthenticated.');
        }

        $allowed = collect($roles)
            ->map(fn (string $role) => UserRole::from($role))
            ->contains(fn (UserRole $role) => $user->hasRole($role));

        if (! $allowed) {
            return $this->deny($request, 403, 'This action is unauthorized.');
        }

        return $next($request);
    }

    private function deny(Request $request, int $status, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], $status);
        }

        abort($status, $message);
    }
}
