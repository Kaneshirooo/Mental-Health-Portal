<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallMessage extends Model
{
    protected $primaryKey = 'message_id';
    public $timestamps    = true;
    const UPDATED_AT      = null;

    protected $fillable = [
        'call_id',
        'sender_id',
        'message_text',
        'created_at',
    ];

    protected $casts = [
        'call_id'    => 'integer',
        'sender_id'  => 'integer',
        'created_at' => 'datetime',
    ];

    public function call()
    {
        return $this->belongsTo(EmergencyCall::class, 'call_id', 'call_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}
