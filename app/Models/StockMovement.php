<?php

namespace App\Models;

use App\Enums\StockCondition;
use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'movement_type' => StockMovementType::class,
        'condition' => StockCondition::class,
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function brand()
    {
        return $this->belongsTo(CylinderBrand::class, 'brand_id');
    }

    public function cylinderType()
    {
        return $this->belongsTo(CylinderType::class, 'cylinder_type_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * The booking a delivery row was for (DELIVERY_OUT only)
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Adds to godown stock (purchase in)
     * Booking rows are reservations, never in or out
     */
    public function isInbound(): bool
    {
        return $this->movement_type->affectsGodown() && $this->quantity > 0;
    }

    /**
     * Takes from godown stock (booking delivered)
     */
    public function isOutbound(): bool
    {
        return $this->movement_type->affectsGodown() && $this->quantity < 0;
    }
}
