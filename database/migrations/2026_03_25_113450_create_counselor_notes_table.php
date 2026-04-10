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
        Schema::create('counselor_notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->foreignId('counselor_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->text('note_text');
            $table->string('recommendation', 500)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselor_notes');
    }
};
