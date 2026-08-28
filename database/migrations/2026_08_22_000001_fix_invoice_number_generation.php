<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_sequences')) {
            Schema::create('invoice_sequences', function (Blueprint $table) {
                $table->id();
                $table->year('year');
                $table->integer('next_number')->default(1);
                $table->timestamps();
                $table->unique('year');
            });
        }

        $years = DB::table('sales')
            ->pluck('created_at')
            ->filter()
            ->map(fn ($createdAt) => Carbon\Carbon::parse($createdAt)->year)
            ->unique()
            ->sort()
            ->values();

        foreach ($years as $year) {
            if (!DB::table('invoice_sequences')->where('year', $year)->exists()) {
                $maxNum = DB::table('sales')
                    ->get(['created_at', 'invoice_number'])
                    ->filter(fn ($sale) => $sale->created_at && Carbon\Carbon::parse($sale->created_at)->year == $year)
                    ->map(function ($sale) {
                        $parts = explode('-', $sale->invoice_number);
                        return isset($parts[2]) ? (int)$parts[2] : 0;
                    })
                    ->max();

                DB::table('invoice_sequences')->insert([
                    'year' => $year,
                    'next_number' => $maxNum + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $currentYear = now()->year;
        if (!DB::table('invoice_sequences')->where('year', $currentYear)->exists()) {
            DB::table('invoice_sequences')->insert([
                'year' => $currentYear,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};