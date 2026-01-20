<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique(); // Unique index prevents duplicates
            $table->string('last_state_requested');
            $table->string('last_lga_requested');
            $table->integer('request_count')->default(1); // Track how many times they used it
            $table->timestamp('last_request_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_recipients');
    }
};
