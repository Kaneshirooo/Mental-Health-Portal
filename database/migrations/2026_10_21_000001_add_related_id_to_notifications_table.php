<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores the id of the record a notification refers to so the
     * "View Details" link can open that exact record instead of a
     * generic dashboard/list page.
     */
    public function up(): void
    {
        if (Schema::hasColumn('notifications', 'related_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('related_id')->nullable()->after('type');
            $table->index('related_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('notifications', 'related_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['related_id']);
            $table->dropColumn('related_id');
        });
    }
};
