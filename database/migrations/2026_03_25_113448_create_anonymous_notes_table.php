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
        Schema::create('anonymous_notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->text('message');
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('counselor_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_notes');
    }
};
