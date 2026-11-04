<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Traits\HasStockMovements;
use App\Traits\HasTruckAndDriver;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory, HasStockMovements, HasTruckAndDriver, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    protected const STOCK_MOVEMENT_TYPE = StockMovementType::PURCHASE_IN;

    protected $casts = [
        'purchase_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            $purchase->purchase_no = static::generatePurchaseNo();
        });
    }

    protected static function generatePurchaseNo(): string
    {
        $yearMonth = now()->format('ym');
        $prefix = 'PUR-'.$yearMonth;

        // Match by prefix (not purchase_date) and include soft-deleted rows, which still hold their unique number
        $last = static::withTrashed()
            ->where('purchase_no', 'like', $prefix.'%')
            ->latest('id')
            ->value('purchase_no');

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        $sequence = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$sequence;
    }
}
