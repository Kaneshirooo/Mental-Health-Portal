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
        Schema::table('users', function (Blueprint $table) {
            // Add identifying info if it doesn't exist
            if (!Schema::hasColumn('users', 'roll_number')) {
                $table->string('roll_number')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('roll_number');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('contact_number');
            }
        });

        // Data Patch: Update all existing students to 'BSIT' as requested
        DB::table('users')
            ->where('user_type', 'student')
            ->update([
                'course' => 'BSIT',
                'semester' => '1st Semester' // Ensuring a default semester for filtering
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['roll_number', 'date_of_birth', 'gender', 'contact_number', 'department']);
        });
    }
};
