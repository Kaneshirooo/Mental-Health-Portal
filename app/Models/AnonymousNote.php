<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AnonymousNoteMessage;

class AnonymousNote extends Model
{
    use HasFactory;

    protected $primaryKey = 'note_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'message',
        'reply',
        'replied_at',
        'counselor_id',
        'status',
        'created_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(AnonymousNoteMessage::class, 'note_id', 'note_id');
    }
}
