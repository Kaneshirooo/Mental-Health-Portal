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
        Schema::table('appointments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('counselor_notes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('assessment_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_scores', 'created_at')) {
                $table->timestamps();
            }
            $table->softDeletes();
        });

        Schema::table('mood_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('mood_logs', 'created_at')) {
                $table->timestamps();
            }
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('counselor_notes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });

        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }
};
