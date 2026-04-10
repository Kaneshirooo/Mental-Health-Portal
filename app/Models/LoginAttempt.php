<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $table = 'login_attempts';
    protected $primaryKey = 'attempt_id';
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'attempt_time',
    ];

    protected $casts = [
        'attempt_time' => 'datetime',
    ];
}
