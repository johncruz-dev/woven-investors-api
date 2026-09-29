<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-read', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.read', 120))
                ->by($this->resolveRateLimitKey($request));
        });

        RateLimiter::for('api-import', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.import', 10))
                ->by($this->resolveRateLimitKey($request));
        });
    }

    private function resolveRateLimitKey(Request $request): string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->getAuthIdentifier();
        }

        $apiKey = $request->header('X-Api-Key');

        if (is_string($apiKey) && $apiKey !== '') {
            return 'api-key:'.hash('sha256', $apiKey);
        }

        return 'ip:'.$request->ip();
    }
}
