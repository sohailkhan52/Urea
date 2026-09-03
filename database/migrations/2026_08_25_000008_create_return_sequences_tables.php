<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales return sequence table
        Schema::create('sales_return_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->integer('next_number')->default(1);
            $table->timestamps();
            
            $table->index('year');
        });

        // Purchase return sequence table
        Schema::create('purchase_return_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->integer('next_number')->default(1);
            $table->timestamps();
            
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_sequences');
        Schema::dropIfExists('purchase_return_sequences');
    }
};
