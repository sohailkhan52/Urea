<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\EnsureMultiWarehouseEnabled;
use App\Http\Middleware\EnsureWarehouseAccess;
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
            'multi_warehouse' => EnsureMultiWarehouseEnabled::class,
            'warehouse_access' => EnsureWarehouseAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'could not find driver')) {
                return response('MySQL support is not available in the bundled PHP runtime. Please reinstall the application.', 503);
            }

            if ($exception instanceof \PDOException || $exception instanceof \Illuminate\Database\QueryException) {
                return response('MySQL is unavailable or the database setup is incomplete. Start MySQL and relaunch the application.', 503);
            }

            return null;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
