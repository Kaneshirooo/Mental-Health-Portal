<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'related_id',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * The id of the record this notification refers to (appointment, note,
     * call, student, ...). Null when the notification has no linkable record.
     */
    public function relatedId(): ?int
    {
        return $this->related_id !== null ? (int) $this->related_id : null;
    }

    /**
     * Build the "View Details" destination for this notification.
     *
     * Returns null when the notification has no meaningful destination.
     */
    public function contextUrl(?string $role = null): ?string
    {
        $role = strtolower((string) ($role ?: 'student'));
        $isStudent = $role === 'student';
        $related = $this->relatedId();

        switch ($this->type) {
            case 'appointment':
                $url = $isStudent
                    ? route('student.appointments')
                    : route('counselor.appointments.index');
                return $url . $this->anchor('appt-card-' . $related);

            case 'clinical_feedback':
                $url = $isStudent
                    ? route('student.appointments')
                    : route('counselor.appointments.index');
                return $url . $this->anchor('appt-card-' . $related);

            case 'note':
                $url = $isStudent
                    ? route('student.notes.index')
                    : route('counselor.notes.index');
                return $url . $this->anchor('note-' . $related);

            case 'emergency':
                if ($isStudent) {
                    return route('emergency');
                }
                return route('counselor.emergency.calls.logs') . $this->anchor('call-' . $related);

            case 'mood_alert':
                if ($isStudent) {
                    return route('student.dashboard');
                }
                return $related
                    ? route('counselor.students.show', $related)
                    : route('counselor.students.index');

            case 'assessment':
                return $isStudent
                    ? route('student.reports.index')
                    : route('counselor.students.index');

            default:
                return null;
        }
    }

    /**
     * Build a #fragment for the related record, when there is one.
     */
    protected function anchor(?string $elementId): string
    {
        if (!$elementId) {
            return '';
        }

        return '#' . preg_replace('/[^A-Za-z0-9\-_]/', '', $elementId);
    }
}
