<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CounselorAvailability extends Model
{
    use HasFactory;

    protected $table = 'counselor_availability';
    protected $primaryKey = 'availability_id';

    protected $fillable = [
        'counselor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }
}
