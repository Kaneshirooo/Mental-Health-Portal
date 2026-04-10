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
        Schema::create('anonymous_note_messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->foreignId('note_id')->constrained('anonymous_notes', 'note_id')->onDelete('cascade');
            $table->enum('sender_type', ['student', 'counselor', 'admin']);
            $table->text('message_text');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_note_messages');
    }
};
