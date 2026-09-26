<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $tables = [
                'users' => 'user_id',
                'appointments' => 'appointment_id',
                'session_logs' => 'log_id',
                'emergency_calls' => 'call_id',
                'call_messages' => 'message_id',
                'user_profiles' => 'profile_id',
            ];
            
            foreach ($tables as $table => $primaryKey) {
                if (Schema::hasTable($table)) {
                    $seqName = "{$table}_{$primaryKey}_seq";
                    try {
                        DB::statement("SELECT setval('{$seqName}', COALESCE((SELECT MAX({$primaryKey}) FROM {$table}), 1));");
                    } catch (\Exception $e) {
                        // Ignore sequence name mismatches
                    }
                }
            }
        }
    }

    public function down(): void
    {
    }
};
