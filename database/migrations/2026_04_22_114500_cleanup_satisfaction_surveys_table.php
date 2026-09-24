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
        Schema::table('satisfaction_surveys', function (Blueprint $table) {
            // Check if columns exist before dropping to prevent errors
            $columnsToDrop = [
                'overall_rating',
                'communication_rating',
                'helpfulness_rating',
                'comfort_rating',
                'would_recommend',
                'feedback_text'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('satisfaction_surveys', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satisfaction_surveys', function (Blueprint $table) {
            $table->integer('overall_rating')->nullable();
            $table->integer('communication_rating')->nullable();
            $table->integer('helpfulness_rating')->nullable();
            $table->integer('comfort_rating')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->text('feedback_text')->nullable();
        });
    }
};
