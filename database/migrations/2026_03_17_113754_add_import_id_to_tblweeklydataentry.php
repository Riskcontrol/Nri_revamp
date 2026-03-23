<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds import_id to tblweeklydataentry so import batches can be traced
 * through both tables.
 *
 * The matching column on tbldataentry was already created by:
 *   database/migrations/2026_01_12_035028_add_import_id_to_tbldataentry.php
 *
 * This migration adds the equivalent column to tblweeklydataentry so that
 * show() and exportImportedIncidents() can also query the weekly table
 * by batch if needed in the future.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblweeklydataentry', function (Blueprint $table) {
            // nullable so older rows (inserted before this migration) are unaffected
            $table->unsignedBigInteger('import_id')->nullable()->after('ID');
            $table->index('import_id');
        });
    }

    public function down(): void
    {
        Schema::table('tblweeklydataentry', function (Blueprint $table) {
            $table->dropIndex(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
