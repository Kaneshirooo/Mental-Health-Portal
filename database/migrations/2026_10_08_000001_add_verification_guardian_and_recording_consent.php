<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_proof_path')->nullable()->after('profile_picture');
            $table->string('verification_status', 20)->default('pending')->after('id_proof_path');
            $table->string('guardian_name')->nullable()->after('verification_status');
            $table->string('guardian_relationship', 100)->nullable()->after('guardian_name');
            $table->string('guardian_contact', 30)->nullable()->after('guardian_relationship');
            $table->string('guardian_email')->nullable()->after('guardian_contact');
        });

        Schema::table('emergency_calls', function (Blueprint $table) {
            $table->boolean('recording_consent')->nullable()->after('status');
            $table->timestamp('recording_consent_at')->nullable()->after('recording_consent');
        });
    }

    public function down(): void
    {
        Schema::table('emergency_calls', function (Blueprint $table) {
            $table->dropColumn(['recording_consent', 'recording_consent_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_proof_path',
                'verification_status',
                'guardian_name',
                'guardian_relationship',
                'guardian_contact',
                'guardian_email',
            ]);
        });
    }
};
