<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->update(['email' => strtolower($user->email)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to undo lowercase, keeping as is
    }
};
