<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a helper table to track purchase number sequences
        Schema::create('purchase_sequences', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->integer('next_number')->default(1);
            $table->timestamps();
            
            // Ensure only one sequence per year
            $table->unique('year');
        });
        
        // Initialize sequences for existing years
        $years = DB::table('purchases')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year');
        
        foreach ($years as $year) {
            $maxNum = DB::table('purchases')
                ->whereYear('created_at', $year)
                ->get()
                ->map(function ($purchase) {
                    $parts = explode('-', $purchase->purchase_number);
                    return isset($parts[2]) ? (int)$parts[2] : 0;
                })
                ->max();
            
            DB::table('purchase_sequences')->insert([
                'year' => $year,
                'next_number' => $maxNum + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Initialize for current year if not exists
        $currentYear = now()->year;
        if (!DB::table('purchase_sequences')->where('year', $currentYear)->exists()) {
            DB::table('purchase_sequences')->insert([
                'year' => $currentYear,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_sequences');
    }
};
