<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            $table->foreignId('score_id')
                ->nullable()
                ->after('question_id')
                ->constrained('assessment_scores', 'score_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('score_id');
        });
    }
};
