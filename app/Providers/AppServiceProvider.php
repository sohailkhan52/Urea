<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        
        $this->configureDefaults();
        $this->registerBladeDirectives();
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
