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
        Schema::create('location_insights', function (Blueprint $table) {
            $table->id();
            $table->string('state');
            $table->unsignedInteger('year');
            $table->json('summary')->nullable();
            $table->json('insights')->nullable();
            $table->string('model')->nullable();
            $table->string('hash', 64)->index();
            $table->string('source')->nullable(); // groq | fallback
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['state', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_insights');
    }
};
