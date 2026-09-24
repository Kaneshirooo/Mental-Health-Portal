<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->increments('conversation_id');
            $table->integer('student_id');
            $table->string('title', 150)->default('New Conversation');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index(['student_id', 'updated_at']);
            $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        Schema::table('chat_history', function (Blueprint $table) {
            $table->unsignedInteger('conversation_id')->nullable()->after('student_id');
            $table->index(['student_id', 'conversation_id']);
            $table->foreign('conversation_id')->references('conversation_id')->on('chat_conversations')->onDelete('cascade');
        });

        $studentIds = DB::table('chat_history')->select('student_id')->distinct()->pluck('student_id');
        foreach ($studentIds as $studentId) {
            $conversationId = DB::table('chat_conversations')->insertGetId([
                'student_id' => $studentId,
                'title' => 'Imported Conversation',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('chat_history')
                ->where('student_id', $studentId)
                ->update(['conversation_id' => $conversationId]);
        }
    }

    public function down(): void
    {
        Schema::table('chat_history', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropIndex(['student_id', 'conversation_id']);
            $table->dropColumn('conversation_id');
        });

        Schema::dropIfExists('chat_conversations');
    }
};
