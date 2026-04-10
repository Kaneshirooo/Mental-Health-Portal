<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case REQUESTED = 'requested';
    case CONFIRMED = 'confirmed';
    case DECLINED = 'declined';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match($this) {
            self::REQUESTED => 'Requested',
            self::CONFIRMED => 'Confirmed',
            self::DECLINED => 'Declined',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::REQUESTED => '#f59e0b', // amber
            self::CONFIRMED => '#10b981', // emerald
            self::DECLINED => '#ef4444', // red
            self::CANCELLED => '#6b7280', // gray
            self::COMPLETED => '#3b82f6', // blue
        };
    }

    public function isActionable(): bool
    {
        return in_array($this, [self::REQUESTED, self::CONFIRMED]);
    }
}
