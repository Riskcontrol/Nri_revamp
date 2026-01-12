<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDataImportTableAddFields extends Migration
{
    public function up()
    {
        Schema::table('data_import', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('data_import', 'total_rows')) {
                $table->integer('total_rows')->default(0)->after('rows_failed');
            }

            if (!Schema::hasColumn('data_import', 'processing_time')) {
                $table->integer('processing_time')->nullable()->after('total_rows');
            }

            if (!Schema::hasColumn('data_import', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('processing_time');
            }

            if (!Schema::hasColumn('data_import', 'status')) {
                $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                    ->default('pending')
                    ->after('user_id');
            }

            // Add timestamps if they don't exist
            if (!Schema::hasColumn('data_import', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('data_import', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Add indexes for better performance
        Schema::table('data_import', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::table('data_import', function (Blueprint $table) {
            $table->dropColumn([
                'total_rows',
                'processing_time',
                'user_id',
                'status'
            ]);

            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
        });
    }
}
