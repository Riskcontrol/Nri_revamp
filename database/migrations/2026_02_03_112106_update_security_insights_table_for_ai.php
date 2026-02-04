<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('security_insights', function (Blueprint $table) {

            // 1. Indicator filter (Composite / Fatalities / Kidnapping etc)
            if (!Schema::hasColumn('security_insights', 'indicator')) {
                $table->string('indicator')->nullable()->after('index_type');
            }

            // 2. Structured summary sent to AI (hash source)
            if (!Schema::hasColumn('security_insights', 'summary')) {
                $table->json('summary')->nullable()->after('indicator');
            }

            // 3. Hash used to detect data changes
            if (!Schema::hasColumn('security_insights', 'hash')) {
                $table->string('hash', 64)->nullable()->after('model');
            }

            // 4. Generated timestamp (nullable for legacy rows)
            if (!Schema::hasColumn('security_insights', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('hash');
            }
        });

        /**
         * UNIQUE INDEX
         * One insight per (year + index_type + indicator)
         */
        Schema::table('security_insights', function (Blueprint $table) {
            $table->unique(
                ['year', 'index_type', 'indicator'],
                'security_insights_unique_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::table('security_insights', function (Blueprint $table) {
            $table->dropUnique('security_insights_unique_scope');

            if (Schema::hasColumn('security_insights', 'indicator')) {
                $table->dropColumn('indicator');
            }

            if (Schema::hasColumn('security_insights', 'summary')) {
                $table->dropColumn('summary');
            }

            if (Schema::hasColumn('security_insights', 'hash')) {
                $table->dropColumn('hash');
            }

            if (Schema::hasColumn('security_insights', 'generated_at')) {
                $table->dropColumn('generated_at');
            }
        });
    }
};
