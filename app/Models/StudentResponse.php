<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AssessmentQuestion;

class StudentResponse extends Model
{
    use HasFactory;

    protected $primaryKey = 'response_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question_id',
        'response_value',
        'assessment_date',
    ];

    protected $casts = [
        'assessment_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id', 'question_id');
    }
}
