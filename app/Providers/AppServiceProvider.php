<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use PDO;
use Throwable;

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
        // Fix for MySQL key length issue (1000 byte limit with utf8mb4)
        Schema::defaultStringLength(191);

        $this->ensureMySqlDatabaseExists();
        $this->configureDefaults();
        $this->registerBladeDirectives();
        $this->registerViewComposers();
<<<<<<< HEAD
        $this->configureNativePhpDatabase();
    }

    protected function ensureMySqlDatabaseExists(): void
    {
        if (config('database.default') !== 'mysql' || ! config('nativephp-internal.running')) {
            return;
        }

        $connection = config('database.connections.mysql');
        $database = (string) ($connection['database'] ?? '');

        if ($database === '' || ! preg_match('/^[A-Za-z0-9_$-]+$/', $database)) {
            return;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $connection['host'] ?? '127.0.0.1',
                $connection['port'] ?? 3306,
                $connection['charset'] ?? 'utf8mb4',
            );

            $pdo = new PDO($dsn, $connection['username'] ?? '', $connection['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $database,
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Keep NativePHP and web mode on the same application database.
     */
    protected function configureNativePhpDatabase(): void
    {
        if (!config('nativephp-internal.running')) {
            return;
        }

        config([
            'database.default' => 'mysql',
            'queue.failed.database' => 'mysql',
            'queue.batching.database' => 'mysql',
            'queue.connections.database.connection' => 'mysql',
        ]);

        DB::purge('mysql');
=======
        $this->registerHorizon();
    }

    /**
     * Register Horizon routes and dashboard.
     */
    protected function registerHorizon(): void
    {
        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            // Use database for Horizon metrics (no Redis required)
            \Laravel\Horizon\Horizon::use('database');
            
            // Authorize Horizon dashboard access
            \Laravel\Horizon\Horizon::auth(function ($request) {
                // Only allow authenticated admin users to access Horizon
                return auth()->check() && auth()->user()->hasRole('admin');
            });

            // Set Horizon prefix for storage keys
            \Laravel\Horizon\Horizon::prefix(env('HORIZON_PREFIX', 'horizon:'));
        }
>>>>>>> b95f2b934ce52ad733e1c31560aec3cd9f57882c
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        // Share multi-warehouse status with layout views
        view()->composer(
            ['layouts.admin', 'layouts.app', 'admin.*'],
            \App\View\Composers\MultiWarehouseComposer::class
        );
    }

    /**
     * Register custom Blade directives for roles and permissions.
     */
    protected function registerBladeDirectives(): void
    {
        // @role('admin')
        \Illuminate\Support\Facades\Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // @permission('users.create')
        \Illuminate\Support\Facades\Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // @anypermission(['users.create', 'users.update'])
        \Illuminate\Support\Facades\Blade::if('anypermission', function ($permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });

        // @allpermissions(['users.create', 'users.update'])
        \Illuminate\Support\Facades\Blade::if('allpermissions', function ($permissions) {
            return auth()->check() && auth()->user()->hasAllPermissions($permissions);
        });

        // @multiwarehouse - Check if multi-warehouse features are enabled
        \Illuminate\Support\Facades\Blade::if('multiwarehouse', function () {
            return isMultiWarehouseEnabled();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}