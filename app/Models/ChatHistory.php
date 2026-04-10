<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $table = 'chat_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'sender',
        'message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
