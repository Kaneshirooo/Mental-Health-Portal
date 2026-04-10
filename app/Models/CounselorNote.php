<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselorNote extends Model
{
    use HasFactory;

    protected $primaryKey = 'note_id';

    protected $fillable = [
        'counselor_id',
        'student_id',
        'note_text',
        'recommendation',
        'follow_up_date',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
