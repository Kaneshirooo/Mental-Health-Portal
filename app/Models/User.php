<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'roll_number',
        'user_type',
        'date_of_birth',
        'gender',
        'contact_number',
        'department',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'user_type' => UserRole::class,
        ];
    }

    public function appointments()
    {
        return $this->user_type->value === 'student' ? $this->studentAppointments() : $this->counselorAppointments();
    }

    public function studentAppointments()
    {
        return $this->hasMany(Appointment::class, 'student_id', 'user_id');
    }

    public function counselorAppointments()
    {
        return $this->hasMany(Appointment::class, 'counselor_id', 'user_id');
    }

    public function moodLogs()
    {
        return $this->hasMany(MoodLog::class, 'student_id', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function anonymousNotes()
    {
        return $this->hasMany(AnonymousNote::class, 'student_id', 'user_id');
    }

    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class, 'user_id', 'user_id');
    }

    public function isCounselor(): bool
    {
        return $this->user_type === UserRole::COUNSELOR;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserRole::ADMIN;
    }

    public function isStudent(): bool
    {
        return $this->user_type === UserRole::STUDENT;
    }

    public function latestAssessment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AssessmentScore::class, 'user_id', 'user_id')->latestOfMany('assessment_date');
    }

    public function getLatestAssessmentAttribute()
    {
        return $this->latestAssessment;
    }
}
