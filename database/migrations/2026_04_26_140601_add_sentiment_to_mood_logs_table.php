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
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->decimal('sentiment_score', 3, 2)->nullable()->after('note');
            $table->string('sentiment_label')->nullable()->after('sentiment_score');
        });
    }

    public function down(): void
    {
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropColumn(['sentiment_score', 'sentiment_label']);
        });
    }
};
