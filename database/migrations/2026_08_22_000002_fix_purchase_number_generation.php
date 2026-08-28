<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_sequences')) {
            Schema::create('purchase_sequences', function (Blueprint $table) {
                $table->id();
                $table->year('year');
                $table->integer('next_number')->default(1);
                $table->timestamps();
                $table->unique('year');
            });
        }
        
        $years = DB::table('purchases')
            ->pluck('created_at')
            ->filter()
            ->map(fn ($createdAt) => Carbon\Carbon::parse($createdAt)->year)
            ->unique()
            ->sort()
            ->values();
        
        foreach ($years as $year) {
            if (!DB::table('purchase_sequences')->where('year', $year)->exists()) {
                $maxNum = DB::table('purchases')
                    ->get(['created_at', 'purchase_number'])
                    ->filter(fn ($purchase) => $purchase->created_at && Carbon\Carbon::parse($purchase->created_at)->year == $year)
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
        }
        
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

    public function down(): void
    {
        Schema::dropIfExists('purchase_sequences');
    }
};