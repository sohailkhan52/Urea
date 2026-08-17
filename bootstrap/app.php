<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Redirect guests to login when accessing protected routes
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        
        // Redirect authenticated users to dashboard when accessing guest routes
        $middleware->redirectUsersTo(fn (Request $request) => route('admin.dashboard'));

        // Register role and permission middleware aliases
        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
            'user_status' => CheckUserStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
