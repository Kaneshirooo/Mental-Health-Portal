<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonymousNoteMessage extends Model
{
    use HasFactory;

    protected $table = 'anonymous_note_messages';
    protected $primaryKey = 'message_id';
    public $timestamps = false;

    protected $fillable = [
        'note_id',
        'sender_type',
        'message_text',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function note()
    {
        return $this->belongsTo(AnonymousNote::class, 'note_id', 'note_id');
    }
}
