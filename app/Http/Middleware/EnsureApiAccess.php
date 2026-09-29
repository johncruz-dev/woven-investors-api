<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.api.auth_required', false)) {
            return $next($request);
        }

        if ($this->authenticateViaApiKey($request)) {
            return $next($request);
        }

        if ($this->authenticateViaSanctum($request)) {
            return $next($request);
        }

        if (config('security.api.log_failed_attempts', true)) {
            Log::warning('API authentication failed.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    private function authenticateViaApiKey(Request $request): bool
    {
        $configuredKey = config('security.api.key');

        if (! is_string($configuredKey) || $configuredKey === '') {
            return false;
        }

        $providedKey = $request->header('X-Api-Key');

        if (! is_string($providedKey) || $providedKey === '') {
            return false;
        }

        return hash_equals($configuredKey, $providedKey);
    }

    private function authenticateViaSanctum(Request $request): bool
    {
        if (! config('security.api.allow_sanctum_tokens', true)) {
            return false;
        }

        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            return false;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken === null) {
            return false;
        }

        $user = $accessToken->tokenable;

        if ($user === null) {
            return false;
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $user);

        return true;
    }
}
