<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\StockMovementType;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(protected StockService $stock) {}

    /**
     * Booking handed over to the customer: its full cylinders leave the godown.
     * Writes one DELIVERY_OUT row per booking item (booking as source, with customer_id + booking_id).
     * The booking row is locked and re-checked inside the transaction, so a double click can't take stock out twice.
     *
     * @return bool false when the booking was no longer pending (nothing changed)
     */
    public function markDelivered(Booking $booking): bool
    {
        $delivered = DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->getKey())->lockForUpdate()->first();

            if ($locked?->status !== BookingStatus::PENDING) {
                return false;
            }

            foreach ($locked->items as $item) {
                $locked->addStock([
                    'brand_id' => $item->brand_id,
                    'cylinder_type_id' => $item->cylinder_type_id,
                    'customer_id' => $locked->customer_id,
                    'booking_id' => $locked->id,
                    'quantity' => $item->quantity,
                ], StockMovementType::DELIVERY_OUT);
            }

            $locked->update(['status' => BookingStatus::DELIVERED, 'is_short_stock' => false]);

            return true;
        });

        if ($delivered) {
            $booking->refresh();
            // Availability changed: re-flag short-stock bookings
            $this->stock->refreshShortStock();
        }

        return $delivered;
    }
}
