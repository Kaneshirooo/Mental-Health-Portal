<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model is in App\Models

class AssessmentScore extends Model
{
    use HasFactory;

    protected $primaryKey = 'score_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'depression_score',
        'anxiety_score',
        'stress_score',
        'overall_score',
        'risk_level',
        'assessment_date',
        'report_generated_at',
        'counselor_notes',
    ];

    protected $casts = [
        'assessment_date' => 'datetime',
        'report_generated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
