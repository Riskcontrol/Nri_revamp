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
        Schema::table('tbldataentry', function (Blueprint $table) {
            $table->unsignedBigInteger('import_id')->nullable()->after('id');
            $table->index('import_id');
        });
    }

    public function down()
    {
        Schema::table('tbldataentry', function (Blueprint $table) {
            $table->dropColumn('import_id');
        });
    }
};
