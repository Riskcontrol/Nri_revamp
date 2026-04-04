<?php

// File: database/migrations/2026_04_01_000002_create_whatsapp_alert_log_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_alert_log', function (Blueprint $table) {
            $table->id();

            // Links back to tbldataentry.eventid (no FK constraint — tbldataentry uses no migrations)
            $table->string('eventid', 50);
            $table->string('phone_number', 20);
            $table->enum('risk_level', ['High', 'Critical']);

            // Twilio message SID returned after a successful send — e.g. SMxxxxxxx
            $table->string('twilio_sid', 50)->nullable();

            // Delivery lifecycle: queued → sent → delivered | failed
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);

            $table->timestamps();

            $table->index(['eventid', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_alert_log');
    }
};
