<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this line
use App\Models\User; // Added this line

class AiPreassessment extends Model
{
    use HasFactory;

    protected $table = 'ai_preassessments';
    protected $primaryKey = 'pre_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'conversation_transcript',
        'form_answers',
        'ai_report',
        'created_at',
    ];

    protected $casts = [
        'form_answers' => 'array',
        'ai_report' => 'array',
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
