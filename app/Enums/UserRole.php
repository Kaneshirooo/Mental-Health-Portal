<?php

namespace App\Enums;

enum UserRole: string
{
    case STUDENT = 'student';
    case COUNSELOR = 'counselor';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::STUDENT => 'Student',
            self::COUNSELOR => 'Counselor',
            self::ADMIN => 'Administrator',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::STUDENT => '#3b82f6', // blue
            self::COUNSELOR => '#10b981', // green
            self::ADMIN => '#f59e0b', // amber
        };
    }
}
