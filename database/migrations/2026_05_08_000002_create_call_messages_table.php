<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('call_messages');
    }
};
