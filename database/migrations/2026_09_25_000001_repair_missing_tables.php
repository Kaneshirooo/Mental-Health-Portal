<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair migration: idempotently creates any tables that may have been
 * skipped in production due to earlier migration failures.
 */
return new class extends Migration
{
    public function up(): void
    {
        // satisfaction_surveys (final schema after cleanup migration)
        if (!Schema::hasTable('satisfaction_surveys')) {
            Schema::create('satisfaction_surveys', function (Blueprint $table) {
                $table->id('survey_id');
                $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->foreignId('counselor_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // emergency_calls
        if (!Schema::hasTable('emergency_calls')) {
            Schema::create('emergency_calls', function (Blueprint $table) {
                $table->id('call_id');
                $table->integer('student_id');
                $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->integer('counselor_id')->nullable();
                $table->foreign('counselor_id')->references('user_id')->on('users')->onDelete('set null');
                $table->enum('status', ['pending', 'active', 'ended', 'declined', 'missed'])->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        // call_messages
        if (!Schema::hasTable('call_messages')) {
            Schema::create('call_messages', function (Blueprint $table) {
                $table->id('message_id');
                $table->unsignedBigInteger('call_id');
                $table->foreign('call_id')->references('call_id')->on('emergency_calls')->onDelete('cascade');
                $table->integer('sender_id');
                $table->foreign('sender_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->text('message_text');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // user_profiles
        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id('profile_id');
                $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->text('bio')->nullable();
                $table->string('specialization')->nullable();
                $table->timestamps();
            });
        }

        // clinical_sessions
        if (!Schema::hasTable('clinical_sessions')) {
            Schema::create('clinical_sessions', function (Blueprint $table) {
                $table->id('session_id');
                $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
                $table->text('session_notes')->nullable();
                $table->string('session_type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('call_messages');
        Schema::dropIfExists('emergency_calls');
        Schema::dropIfExists('satisfaction_surveys');
        Schema::dropIfExists('clinical_sessions');
        Schema::dropIfExists('user_profiles');
    }
};
