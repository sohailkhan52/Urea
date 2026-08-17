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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Customer type: farmer, dealer, retail_customer
            $table->enum('customer_type', ['farmer', 'dealer', 'retail_customer'])
                  ->default('retail_customer')
                  ->index();
            
            // Basic Information
            $table->string('name')
                  ->index();
            $table->string('father_name')
                  ->nullable();
            
            // Identity
            $table->string('cnic')
                  ->nullable()
                  ->unique();
            
            // Contact Information
            $table->string('phone')
                  ->nullable()
                  ->index();
            $table->string('email')
                  ->nullable()
                  ->unique();
            
            // Address Information
            $table->string('village')
                  ->nullable();
            $table->string('city')
                  ->nullable()
                  ->index();
            $table->text('address')
                  ->nullable();
            
            // Credit and Status
            $table->decimal('credit_limit', 12, 2)
                  ->default(0);
            $table->enum('status', ['active', 'inactive'])
                  ->default('active')
                  ->index();
            
            // Audit
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for filtering
            $table->index(['customer_type', 'status']);
            $table->index(['city', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
