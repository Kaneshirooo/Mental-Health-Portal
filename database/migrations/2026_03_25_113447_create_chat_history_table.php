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
        Schema::create('chat_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('sender', ['user', 'aria']);
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();

            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_history');
    }
};
