<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case DELIVERED = 'delivered';

    public static function getStatuses(): array
    {
        return [
            self::PENDING,
            self::CANCELLED,
            self::DELIVERED,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
            self::DELIVERED => 'Delivered',
        };
    }
}
