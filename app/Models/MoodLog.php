<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this line

class MoodLog extends Model
{
    use HasFactory; // Added this line

    protected $primaryKey = 'log_id'; // Added this line
    public $timestamps = false; // Added this line

    protected $fillable = [ // Added this block
        'student_id',
        'mood_score',
        'mood_emoji',
        'note',
        'logged_at',
    ];

    protected $casts = [ // Added this block
        'logged_at' => 'datetime',
    ];

    public function student() // Added this method
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
