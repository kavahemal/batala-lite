<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\StockCondition;
use App\Enums\StockMovementType;
use App\Models\Booking;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Stock figures, keyed by cylinder_type_id (a cylinder type belongs to exactly one brand)
 */
class StockService
{
    /**
     * Cylinders in the godown per type: purchases in, deliveries out (not bookings)
     * Pass $exceptSource (e.g. the purchase being edited) to leave its own movements out
     *
     * @return Collection<int, int> cylinder_type_id => quantity
     */
    public function godown(StockCondition $condition = StockCondition::FULL, ?Model $exceptSource = null): Collection
    {
        return StockMovement::query()
            ->whereNotIn('movement_type', StockMovementType::notInGodown())
            ->where('condition', $condition)
            ->when($exceptSource?->exists, fn ($query) => $query->whereNot(fn ($query) => $query
                ->where('source_type', $exceptSource->getMorphClass())
                ->where('source_id', $exceptSource->getKey())))
            ->groupBy('cylinder_type_id')
            ->selectRaw('cylinder_type_id, SUM(quantity) as total')
            ->pluck('total', 'cylinder_type_id')
            ->map(fn ($total) => (int) $total);
    }

    /**
     * Full cylinders booked but not delivered yet (pending bookings)
     *
     * @return Collection<int, int> cylinder_type_id => quantity
     */
    public function reserved(?int $exceptBookingId = null): Collection
    {
        return StockMovement::query()
            ->join('bookings', function ($join) {
                $join->on('bookings.id', '=', 'stock_movements.source_id')
                    ->where('stock_movements.source_type', Booking::class);
            })
            ->where('stock_movements.movement_type', StockMovementType::BOOKING)
            ->where('bookings.status', BookingStatus::PENDING)
            ->whereNull('bookings.deleted_at')
            ->when($exceptBookingId, fn ($query) => $query->where('bookings.id', '!=', $exceptBookingId))
            ->groupBy('stock_movements.cylinder_type_id')
            ->selectRaw('stock_movements.cylinder_type_id, SUM(stock_movements.quantity) as total')
            ->pluck('total', 'cylinder_type_id')
            ->map(fn ($total) => (int) $total);
    }

    /**
     * Full cylinders free to book = full in godown - reserved
     * Pass the booking being edited so it doesn't reserve against itself
     *
     * @return Collection<int, int> cylinder_type_id => quantity (can be negative when overbooked)
     */
    public function available(?int $exceptBookingId = null): Collection
    {
        $godown = $this->godown();
        $reserved = $this->reserved($exceptBookingId);

        return $godown->keys()->merge($reserved->keys())->unique()
            ->mapWithKeys(fn ($typeId) => [$typeId => ($godown[$typeId] ?? 0) - ($reserved[$typeId] ?? 0)]);
    }

    /**
     * Re-flag pending bookings that the godown can't cover, first come first served per cylinder type (by booking date).
     * Call after anything that changes availability or the order (booking/purchase saved or deleted, booking marked delivered).
     *
     * @return int number of short bookings
     */
    public function refreshShortStock(): int
    {
        $remaining = $this->godown()->all();
        $shortIds = [];

        $openBookings = Booking::where('status', BookingStatus::PENDING)
            ->with('items')
            ->orderBy('booking_date')
            ->orderBy('id')
            ->get();

        foreach ($openBookings as $booking) {
            foreach ($booking->items as $item) {
                $remaining[$item->cylinder_type_id] = ($remaining[$item->cylinder_type_id] ?? 0) - $item->quantity;

                if ($remaining[$item->cylinder_type_id] < 0) {
                    $shortIds[$booking->id] = true;
                }
            }
        }

        $shortIds = array_keys($shortIds);

        DB::transaction(function () use ($shortIds) {
            Booking::whereNotIn('id', $shortIds)->where('is_short_stock', true)->update(['is_short_stock' => false]);
            Booking::whereIn('id', $shortIds)->where('is_short_stock', false)->update(['is_short_stock' => true]);
        });

        return count($shortIds);
    }
}
