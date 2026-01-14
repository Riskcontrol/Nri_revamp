<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbldataentry', function (Blueprint $table) {
            // 1. Core Speed Index (LGA + Year)
            // Fix: We explicitly limit BOTH columns to 20 characters.
            // This forces the index size to be tiny (~160 bytes), guaranteed to pass.
            DB::statement('CREATE INDEX idx_lga_year ON tbldataentry (lga(20), eventyear(20))');

            // 2. State Benchmark Index (Location + Year)
            DB::statement('CREATE INDEX idx_state_year ON tbldataentry (location(20), eventyear(20))');

            // 3. Risk Breakdown Index
            DB::statement('CREATE INDEX idx_lga_year_risk ON tbldataentry (lga(20), eventyear(20), riskindicators(20))');

            // 4. Date Index (Standard, usually safe)
            // If this fails, your eventdateToUse might be TEXT instead of DATE.
            // If so, change this line to: DB::statement('CREATE INDEX idx_event_date ON tbldataentry (eventdateToUse(20))');
            DB::statement('CREATE INDEX idx_event_date ON tbldataentry (eventdateToUse)');

            // 5. Hotspots Index
            DB::statement('CREATE INDEX idx_lga_year_neighbourhood ON tbldataentry (lga(20), eventyear(20), neighbourhood(20))');
        });
    }

    public function down(): void
    {
        Schema::table('tbldataentry', function (Blueprint $table) {
            // We use an array to catch potential errors if an index doesn't exist
            $indexes = [
                'idx_lga_year_neighbourhood',
                'idx_event_date',
                'idx_lga_year_risk',
                'idx_state_year',
                'idx_lga_year'
            ];

            foreach ($indexes as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $e) {
                    // Continue if index not found
                }
            }
        });
    }
};
