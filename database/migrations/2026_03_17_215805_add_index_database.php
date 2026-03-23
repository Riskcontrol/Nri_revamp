<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // eventid is likely TEXT/VARCHAR-like. Prefix index is safest if TEXT.
        DB::statement('ALTER TABLE tbldataentry ADD INDEX idx_tbldataentry_eventid (eventid(191))');
        DB::statement('ALTER TABLE tblweeklydataentry ADD INDEX idx_tblweeklydataentry_eventid (eventid(191))');

        // Helpful page filters / sorts
        DB::statement('ALTER TABLE tbldataentry ADD INDEX idx_tbldataentry_eventyear (eventyear)');
        DB::statement('ALTER TABLE tbldataentry ADD INDEX idx_tbldataentry_impact (impact)');
        DB::statement('ALTER TABLE tbldataentry ADD INDEX idx_tbldataentry_import_id (import_id)');
        DB::statement('ALTER TABLE tblweeklydataentry ADD INDEX idx_tblweeklydataentry_news (news)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tbldataentry DROP INDEX idx_tbldataentry_eventid');
        DB::statement('ALTER TABLE tblweeklydataentry DROP INDEX idx_tblweeklydataentry_eventid');

        DB::statement('ALTER TABLE tbldataentry DROP INDEX idx_tbldataentry_eventyear');
        DB::statement('ALTER TABLE tbldataentry DROP INDEX idx_tbldataentry_impact');
        DB::statement('ALTER TABLE tbldataentry DROP INDEX idx_tbldataentry_import_id');
        DB::statement('ALTER TABLE tblweeklydataentry DROP INDEX idx_tblweeklydataentry_news');
    }
};
