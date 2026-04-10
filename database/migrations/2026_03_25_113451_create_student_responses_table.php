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
        Schema::create('student_responses', function (Blueprint $table) {
            $table->id('response_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assessment_questions', 'question_id')->onDelete('cascade');
            $table->integer('response_value');
            $table->timestamp('assessment_date')->useCurrent();

            $table->index(['user_id', 'assessment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_responses');
    }
};
