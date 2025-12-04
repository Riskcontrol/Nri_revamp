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
        Schema::table('tbldataentry', function (Blueprint $table) {
        $table->index('yy');             // Years
        $table->index('location');       // States
        $table->index('month_pro');      // Timeline
        $table->index('riskindicators');
        $table->index('riskfactors');
        $table->index('motive');
        $table->index('weapon_type');
        $table->index('attack_group_name');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
