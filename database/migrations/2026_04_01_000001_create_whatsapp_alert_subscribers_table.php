<?php

// File: database/migrations/2026_04_01_000001_create_whatsapp_alert_subscribers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_alert_subscribers', function (Blueprint $table) {
            $table->id();

            // Phone number in E.164 format — +2348012345678
            $table->string('phone_number')->unique();
            $table->string('name')->nullable();

            // Alert level preference
            // 'all'      → receive High + Critical alerts
            // 'critical' → receive Critical only
            $table->enum('subscription_tier', ['all', 'critical'])->default('all');

            // Optional JSON array of state names, e.g. ["Lagos","Abuja"]
            // NULL means all states
            $table->json('state_filter')->nullable();

            // Opt-in / opt-out tracking (WhatsApp compliance requires confirmed opt-in)
            $table->boolean('is_active')->default(false); // only true after opt-in confirmed
            $table->string('opt_in_token', 64)->unique()->nullable();
            $table->timestamp('opted_in_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'subscription_tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_alert_subscribers');
    }
};
