<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('new_reports', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('period')->nullable();
            $table->text('description')->nullable();

            // optional thumbnail shown on reports page
            $table->string('image_path')->nullable();

            // stored privately; only downloadable via controller
            $table->string('file_path'); // e.g. private/reports/2024-security.pdf

            // Tier gating: 1 = basic registered, 2+ premium, etc.
            $table->unsignedTinyInteger('min_tier')->default(1);

            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['is_published', 'min_tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_reports');
    }
};
