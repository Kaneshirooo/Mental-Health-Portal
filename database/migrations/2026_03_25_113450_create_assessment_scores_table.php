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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id('score_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->integer('depression_score')->nullable();
            $table->integer('anxiety_score')->nullable();
            $table->integer('stress_score')->nullable();
            $table->integer('overall_score')->nullable();
            $table->enum('risk_level', ['Low', 'Moderate', 'High', 'Critical']);
            $table->timestamp('assessment_date')->useCurrent();
            $table->timestamp('report_generated_at')->useCurrent();
            $table->text('counselor_notes')->nullable();

            $table->index(['user_id', 'assessment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
