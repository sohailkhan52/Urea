<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create products table based on the modal view requirements:
     * - name (required)
     * - unit (required, KG/MG/Piece)
     * - purchase_price (required)
     * - sale_price (required)
     */
    public function up(): void
    {
        // Drop the existing products table if it exists
        Schema::dropIfExists('products');
        
        // Create new products table with only the fields needed by the view
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('unit', ['KG', 'MG', 'Piece'])->default('KG');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('name');
            $table->index('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};