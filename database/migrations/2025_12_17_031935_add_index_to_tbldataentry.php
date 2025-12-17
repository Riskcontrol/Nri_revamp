<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
DB::statement('CREATE INDEX idx_dataentry_filter ON tbldataentry (yy, location(50), riskindicators(50))');
}

public function down()
{
    Schema::table('tbldataentry', function (Blueprint $table) {
        $table->dropIndex('idx_dataentry_filter');
    });
}
};
