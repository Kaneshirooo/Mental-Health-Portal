<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'student_id',
        'counselor_id',
        'scheduled_at',
        'duration_min',
        'status',
        'reason',
        'counselor_message',
        'is_priority',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_priority' => 'boolean',
        'status' => AppointmentStatus::class,
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'user_id');
    }

    public function isHighPriority(): bool
    {
        return $this->is_priority || $this->status === AppointmentStatus::REQUESTED;
    }
}
