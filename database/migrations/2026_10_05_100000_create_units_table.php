<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create units table - master list of all available measurement units.
     * This table stores generic units (KG, Piece, Litre, etc.) that can be
     * assigned to products through the product_units table.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            
            // Basic unit information
            $table->string('name', 100)->unique();
            $table->string('abbreviation', 20);
            
            // Category for grouping (weight, volume, count, packaging)
            $table->enum('category', ['weight', 'volume', 'count', 'packaging'])
                  ->index()
                  ->comment('Unit category for logical grouping');
            
            // Status
            $table->boolean('is_active')->default(true)->index();
            
            // Optional description
            $table->string('description', 255)->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('abbreviation');
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
