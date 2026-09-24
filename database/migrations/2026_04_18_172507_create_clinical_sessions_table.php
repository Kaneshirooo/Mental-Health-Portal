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
        Schema::create('clinical_sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
            
            $table->unsignedInteger('counselor_id');
            $table->foreign('counselor_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Event Metrics
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->enum('modality', ['in-person', 'video', 'chat', 'voice'])->default('in-person');
            $table->enum('session_type', ['assessment', 'counseling', 'emergency', 'follow-up'])->default('counseling');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_sessions');
    }
};
