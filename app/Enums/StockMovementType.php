<?php

namespace App\Enums;

enum StockMovementType: string
{
    case BOOKING = 'booking';
    case PURCHASE_IN = 'purchase_in';
    case DELIVERY_OUT = 'delivery_out';
    case EMPTY_RETURN_IN = 'empty_return_in';
    case EMPTY_RETURN_OUT = 'empty_return_out';
    case DAMAGED_RETURN_OUT = 'damaged_return_out';
    case ADJUSTMENT = 'adjustment';
    case CUSTOMER_OPENING = 'customer_opening';

    public static function allTypes(): array
    {
        return [
            self::BOOKING,
            self::PURCHASE_IN,
            self::DELIVERY_OUT,
            self::EMPTY_RETURN_IN,
            self::EMPTY_RETURN_OUT,
            self::DAMAGED_RETURN_OUT,
            self::ADJUSTMENT,
            self::CUSTOMER_OPENING,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::BOOKING => 'Booking',
            self::PURCHASE_IN => 'Purchase In',
            self::DELIVERY_OUT => 'Delivery Out',
            self::EMPTY_RETURN_IN => 'Empty Return In',
            self::EMPTY_RETURN_OUT => 'Empty Return Out',
            self::DAMAGED_RETURN_OUT => 'Damaged Return Out',
            self::ADJUSTMENT => 'Stock Adjustment',
            self::CUSTOMER_OPENING => 'Customer Opening',
        };
    }

    /**
     * Quantity sign: +1 adds to godown stock, -1 removes it, null keeps the sign given (adjustments)
     * Customer opening is negative like a delivery: cylinders with the customer that they still owe back
     */
    public function sign(): ?int
    {
        return match ($this) {
            self::BOOKING, self::PURCHASE_IN, self::EMPTY_RETURN_IN => 1,
            self::DELIVERY_OUT, self::EMPTY_RETURN_OUT, self::DAMAGED_RETURN_OUT, self::CUSTOMER_OPENING => -1,
            self::ADJUSTMENT => null,
        };
    }

    /**
     * Default cylinder condition for this movement (adjustments must pass it explicitly)
     */
    public function condition(): StockCondition
    {
        return match ($this) {
            self::EMPTY_RETURN_IN, self::EMPTY_RETURN_OUT => StockCondition::EMPTY,
            self::DAMAGED_RETURN_OUT => StockCondition::DAMAGED,
            default => StockCondition::FULL,
        };
    }

    /**
     * Booking rows are ordered quantities (reservations) and customer opening rows are cylinders
     * already with customers before the system started - neither is godown stock
     */
    public function affectsGodown(): bool
    {
        return ! in_array($this, self::notInGodown());
    }

    /**
     * Types that never count in godown stock
     *
     * @return array<int, self>
     */
    public static function notInGodown(): array
    {
        return [self::BOOKING, self::CUSTOMER_OPENING];
    }
}
