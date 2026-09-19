<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Accepted => 'Diterima',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    /** @return list<self> */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Accepted, self::Cancelled],
            self::Accepted => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    /** @return list<self> */
    public static function countedTowardsCapacity(): array
    {
        return [self::Pending, self::Accepted, self::Completed];
    }
}
