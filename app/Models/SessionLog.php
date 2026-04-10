<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Added for the user relationship

class SessionLog extends Model
{
    use HasFactory;

    protected $table = 'session_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false; // Using login_time and logout_time manually

    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'activity',
    ];

    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
