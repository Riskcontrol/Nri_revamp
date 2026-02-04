<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_insights', function (Blueprint $table) {
            $table->id();
            $table->string('index_type'); // Composite Risk Index, Terrorism Index, Kidnapping Index
            $table->unsignedInteger('year');
            $table->json('insights'); // [{title,text}, ...]
            $table->string('model')->nullable(); // groq model name
            $table->string('hash')->index(); // hash of input summary so we don't regen unnecessarily
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['index_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_insights');
    }
};
