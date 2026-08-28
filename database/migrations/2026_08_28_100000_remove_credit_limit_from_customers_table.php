<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('customers', 'credit_limit')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->dropColumn('credit_limit');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('customers', 'credit_limit')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->decimal('credit_limit', 12, 2)->default(0);
            });
        }
    }
};
