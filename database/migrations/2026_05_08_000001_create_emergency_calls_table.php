<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('emergency_calls');
    }
};
