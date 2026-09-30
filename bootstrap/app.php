<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);

        $middleware->alias([
            'api.access' => \App\Http\Middleware\EnsureApiAccess::class,
            'dashboard.access' => \App\Http\Middleware\EnsureDashboardAccess::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\InvalidArgumentException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });
    })->create();
