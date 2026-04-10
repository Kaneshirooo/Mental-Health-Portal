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
        Schema::create('ai_preassessments', function (Blueprint $table) {
            $table->id('pre_id');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->mediumText('conversation_transcript')->nullable();
            $table->text('form_answers')->nullable();
            $table->text('ai_report')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_preassessments');
    }
};
