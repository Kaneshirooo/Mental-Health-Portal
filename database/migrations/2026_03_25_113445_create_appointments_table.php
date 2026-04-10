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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('counselor_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->integer('duration_min')->default(30);
            $table->enum('status', ['requested', 'confirmed', 'declined', 'cancelled', 'completed'])->default('requested');
            $table->text('reason')->nullable();
            $table->text('counselor_message')->nullable();
            $table->boolean('is_priority')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'scheduled_at']);
            $table->index(['counselor_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
