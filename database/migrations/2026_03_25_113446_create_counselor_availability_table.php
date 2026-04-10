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
        Schema::create('counselor_availability', function (Blueprint $table) {
            $table->id('availability_id');
            $table->foreignId('counselor_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0=Sun,1=Mon,...,6=Sat');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['counselor_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselor_availability');
    }
};
