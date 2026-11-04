<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\StockMovementType;
use App\Traits\HasStockMovements;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, HasStockMovements, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    protected const STOCK_MOVEMENT_TYPE = StockMovementType::BOOKING;

    protected $casts = [
        'booking_date' => 'date',
        'scheduled_date' => 'date',
        'status' => BookingStatus::class,
        'is_short_stock' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->booking_no = static::generateBookingNo();
        });
    }

    protected static function generateBookingNo(): string
    {
        $yearMonth = now()->format('ym');
        $prefix = 'BKG-'.$yearMonth;

        // Match by prefix (not booking_date) and include soft-deleted rows, which still hold their unique number
        $last = static::withTrashed()
            ->where('booking_no', 'like', $prefix.'%')
            ->latest('id')
            ->value('booking_no');

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        $sequence = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$sequence;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Delivered bookings are locked: their stock has left the godown
     */
    public function isLocked(): bool
    {
        return $this->status === BookingStatus::DELIVERED;
    }
}
