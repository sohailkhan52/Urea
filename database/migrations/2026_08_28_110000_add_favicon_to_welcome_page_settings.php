<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('welcome_page_settings', 'favicon')) {
            Schema::table('welcome_page_settings', function (Blueprint $table): void {
                $table->string('favicon')->nullable()->after('company_logo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('welcome_page_settings', 'favicon')) {
            Schema::table('welcome_page_settings', function (Blueprint $table): void {
                $table->dropColumn('favicon');
            });
        }
    }
};
