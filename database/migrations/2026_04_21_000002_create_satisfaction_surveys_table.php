<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id('survey_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('counselor_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->unsignedTinyInteger('overall_rating')->comment('1-5 stars');
            $table->unsignedTinyInteger('communication_rating')->nullable()->comment('1-5');
            $table->unsignedTinyInteger('helpfulness_rating')->nullable()->comment('1-5');
            $table->unsignedTinyInteger('comfort_rating')->nullable()->comment('1-5');
            $table->text('feedback_text')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
    }
};
