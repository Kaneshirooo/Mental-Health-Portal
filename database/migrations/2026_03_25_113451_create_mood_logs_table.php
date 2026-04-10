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
        Schema::create('mood_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('student_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->tinyInteger('mood_score')->comment('1=Sad/Critical, 5=Happy/Great');
            $table->string('mood_emoji', 10);
            $table->text('note')->nullable();
            $table->timestamp('logged_at')->useCurrent();

            $table->index(['student_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_logs');
    }
};
