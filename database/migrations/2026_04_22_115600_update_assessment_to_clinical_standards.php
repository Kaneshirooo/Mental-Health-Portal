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
        // Disable foreign key checks to allow clearing the tables
        Schema::disableForeignKeyConstraints();

        // Clear existing responses (since questions are changing completely)
        DB::table('student_responses')->truncate();
        
        // Clear existing questions
        DB::table('assessment_questions')->truncate();

        $questions = [
            // PHQ-9 (Depression) - Timeframe: Last 2 weeks
            ['category' => 'Depression', 'question_text' => 'Little interest or pleasure in doing things', 'question_number' => 1],
            ['category' => 'Depression', 'question_text' => 'Feeling down, depressed, or hopeless', 'question_number' => 2],
            ['category' => 'Depression', 'question_text' => 'Trouble falling or staying asleep, or sleeping too much', 'question_number' => 3],
            ['category' => 'Depression', 'question_text' => 'Feeling tired or having little energy', 'question_number' => 4],
            ['category' => 'Depression', 'question_text' => 'Poor appetite or overeating', 'question_number' => 5],
            ['category' => 'Depression', 'question_text' => 'Feeling bad about yourself — or that you are a failure or have let yourself or your family down', 'question_number' => 6],
            ['category' => 'Depression', 'question_text' => 'Trouble concentrating on things, such as reading the newspaper or watching television', 'question_number' => 7],
            ['category' => 'Depression', 'question_text' => 'Moving or speaking so slowly that other people could have noticed? Or the opposite — being so fidgety or restless that you have been moving around a lot more than usual', 'question_number' => 8],
            ['category' => 'Depression', 'question_text' => 'Thoughts that you would be better off dead or of hurting yourself in some way', 'question_number' => 9],

            // GAD-7 (Anxiety) - Timeframe: Last 2 weeks
            ['category' => 'Anxiety', 'question_text' => 'Feeling nervous, anxious, or on edge', 'question_number' => 10],
            ['category' => 'Anxiety', 'question_text' => 'Not being able to stop or control worrying', 'question_number' => 11],
            ['category' => 'Anxiety', 'question_text' => 'Worrying too much about different things', 'question_number' => 12],
            ['category' => 'Anxiety', 'question_text' => 'Trouble relaxing', 'question_number' => 13],
            ['category' => 'Anxiety', 'question_text' => 'Being so restless that it is hard to sit still', 'question_number' => 14],
            ['category' => 'Anxiety', 'question_text' => 'Becoming easily annoyed or irritable', 'question_number' => 15],
            ['category' => 'Anxiety', 'question_text' => 'Feeling afraid as if something awful might happen', 'question_number' => 16],

            // DASS-21 (Stress) - Timeframe: Last week (Aligned to 2 weeks for consistency)
            ['category' => 'Stress', 'question_text' => 'I found it hard to wind down', 'question_number' => 17],
            ['category' => 'Stress', 'question_text' => 'I tended to over-react to situations', 'question_number' => 18],
            ['category' => 'Stress', 'question_text' => 'I felt like I was using a lot of nervous energy', 'question_number' => 19],
            ['category' => 'Stress', 'question_text' => 'I found myself getting agitated', 'question_number' => 20],
            ['category' => 'Stress', 'question_text' => 'I found it difficult to relax', 'question_number' => 21],
            ['category' => 'Stress', 'question_text' => 'I was intolerant of anything that kept me from getting on with what I was doing', 'question_number' => 22],
            ['category' => 'Stress', 'question_text' => 'I felt that I was rather touchy', 'question_number' => 23],
        ];

        DB::table('assessment_questions')->insert($questions);

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('assessment_questions')->truncate();
    }
};
