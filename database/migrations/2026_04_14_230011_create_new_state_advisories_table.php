<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the state_advisories table.
 *
 * This is the ONLY migration you need for the advisory feature.
 * It replaces these two earlier files — delete them before running this:
 *
 *   database/migrations/2026_04_14_230011_create_state_advisories_table.php   (v1 — year-based)
 *   database/migrations/2026_04_15_000001_create_state_advisories_table_v2.php (v2 — window_end)
 *
 * Steps:
 *   1. Delete both files above from your migrations folder.
 *   2. If you already ran either of them, drop the table first:
 *        php artisan db:table state_advisories   (check if it exists)
 *        Then in MySQL: DROP TABLE state_advisories;
 *   3. Place this file in database/migrations/
 *   4. Run: php artisan migrate
 *
 * ─── Schema design ────────────────────────────────────────────────────────────
 *
 * Unique key: (state, window_end)
 *
 * window_end is the last day of the rolling 12-month window — always "today"
 * when the advisory is generated. This means:
 *
 *   • One advisory per state per day.
 *   • Each night the cron generates a new row for today's date.
 *   • Old rows stay as history (you can prune rows older than 90 days if needed).
 *   • No manual cache invalidation required — the key naturally advances each day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('state_advisories', function (Blueprint $table) {
            $table->id();

            // e.g. "Lagos", "Cross River" — Title Case, matches StateInsight.state
            $table->string('state', 80);

            // The last day of the rolling 12-month window (= date generated)
            // e.g. "2026-04-15" — stored as DATE, not DATETIME
            $table->date('window_end');

            // Risk level 1–4 — stored flat for fast filtering / sorting
            $table->unsignedTinyInteger('risk_level')->default(1);

            // Weighted risk score 0–100 from CalculatesRisk trait
            $table->decimal('risk_score', 5, 2)->default(0.00);

            // Full AI-generated advisory — shape defined in AdvisoryInsightGenerator:
            // { advisory_level, advisory_label, current_situation,
            //   key_risk_signals[], operational_guidance[], generated_at }
            $table->json('advisory_json');

            // The aggregated data payload that was sent TO the AI.
            // Stored for auditing so you can always see what drove the advisory text.
            $table->json('payload_json')->nullable();

            // Which AI model produced this — e.g. "llama-3.1-70b-versatile"
            $table->string('ai_model', 120)->nullable();

            // SHA-256 hash of payload_json.
            // If the hash matches on the next run, the AI call is skipped entirely.
            $table->string('payload_hash', 64)->nullable();

            // When the AI actually generated this (separate from Laravel's updated_at)
            $table->timestamp('generated_at')->nullable();

            $table->timestamps(); // created_at, updated_at

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index('state');
            $table->index('window_end');
            $table->index('risk_level');
            $table->index('payload_hash');

            // Primary uniqueness constraint — one advisory per state per day
            $table->unique(['state', 'window_end'], 'state_advisories_state_window_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_advisories');
    }
};
