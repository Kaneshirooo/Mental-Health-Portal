<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionSurvey extends Model
{
    use HasFactory;

    protected $primaryKey = 'survey_id';

    protected $fillable = [
        'appointment_id',
        'student_id',
        'counselor_id',
        'cc1',
        'cc2',
        'cc3',
        'sqd0',
        'sqd1',
        'sqd2',
        'sqd3',
        'sqd4',
        'sqd5',
        'sqd6',
        'sqd7',
        'sqd8',
        'suggestions',
        'contact_info',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }
}
