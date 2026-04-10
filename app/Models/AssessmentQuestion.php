<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $primaryKey = 'question_id';
    public $timestamps = false;

    protected $fillable = [
        'category',
        'question_text',
        'question_number',
        'created_at',
    ];

    public function studentResponses()
    {
        return $this->hasMany(StudentResponse::class, 'question_id', 'question_id');
    }
}
