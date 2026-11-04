<?php

namespace App\Enums;

enum StockCondition: string
{
    case FULL = 'full';
    case EMPTY = 'empty';
    // Leaking/broken: still in the godown but can't be filled or booked, waits to go back to the company
    case DAMAGED = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'Full',
            self::EMPTY => 'Empty',
            self::DAMAGED => 'Damaged',
        };
    }
}
