<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->seedFirstRunData();
        Window::open();
    }

    protected function seedFirstRunData(): void
    {
        if (app()->runningInConsole() || ! config('nativephp-internal.running')) {
            return;
        }

        try {
            $email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');

            if (Schema::hasTable('users') && ! User::where('email', $email)->exists()) {
                Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\DatabaseSeeder',
                    '--force' => true,
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
