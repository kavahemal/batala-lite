<?php

namespace App\Traits;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasStockMovements
{
    /**
     * All stock movements of this record (every movement type)
     */
    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'source')->with('brand', 'cylinderType');
    }

    /**
     * Movements of the model's main type (STOCK_MOVEMENT_TYPE), e.g. booking lines, purchase lines
     */
    public function items()
    {
        return $this->movements()->where('movement_type', $this->stockMovementType());
    }

    /**
     * The model's main movement type
     */
    protected function stockMovementType(): StockMovementType
    {
        $class = get_class($this);

        if (! defined($class.'::STOCK_MOVEMENT_TYPE')) {
            throw new Exception('STOCK_MOVEMENT_TYPE not defined in model');
        }

        return constant($class.'::STOCK_MOVEMENT_TYPE');
    }

    /**
     * Create a stock movement
     * Sets movement_type, condition and the signed quantity from the movement type
     */
    public function addStock(array $data, ?StockMovementType $type = null): StockMovement
    {
        $type ??= $this->stockMovementType();

        $data['movement_type'] = $type;
        $data['condition'] ??= $type->condition();
        $data['quantity'] = $this->signedQuantity($type, $data['quantity']);

        return DB::transaction(function () use ($data) {
            return $this->movements()->create($data);
        });
    }

    /**
     * Update existing stock movement safely
     */
    public function updateStock(StockMovement $movement, array $data): StockMovement
    {
        $this->ensureOwnMovement($movement);

        $newQuantity = $this->signedQuantity($movement->movement_type, $data['quantity']);

        return DB::transaction(function () use ($movement, $newQuantity) {
            $movement->quantity = $newQuantity;

            return $movement->save() ? $movement : throw new Exception('Failed to update stock movement');
        });
    }

    /**
     * Delete stock movement safely (soft delete)
     */
    public function deleteStock(StockMovement $movement): bool
    {
        $this->ensureOwnMovement($movement);

        return DB::transaction(function () use ($movement) {
            return $movement->delete();
        });
    }

    /**
     * Sync multiple stock items of one movement type at once
     * Creates or updates by brand_id + cylinder_type_id (+ customer_id / condition when given),
     * and removes rows of that type that are no longer in $items
     */
    public function syncStockItems(array $items, ?StockMovementType $type = null): void
    {
        $type ??= $this->stockMovementType();

        DB::transaction(function () use ($items, $type) {
            $keptIds = [];

            foreach ($items as $item) {
                if (empty($item['brand_id']) || empty($item['cylinder_type_id']) || empty($item['quantity'])) {
                    continue;
                }

                $movement = $this->movements()
                    ->where('movement_type', $type)
                    ->where('brand_id', $item['brand_id'])
                    ->where('cylinder_type_id', $item['cylinder_type_id'])
                    ->when(array_key_exists('customer_id', $item), fn ($query) => $query->where('customer_id', $item['customer_id']))
                    ->when(array_key_exists('condition', $item), fn ($query) => $query->where('condition', $item['condition']))
                    ->first();

                if ($movement) {
                    $this->updateStock($movement, ['quantity' => $item['quantity']]);
                } else {
                    $movement = $this->addStock($item, $type);
                }

                $keptIds[] = $movement->id;
            }

            // Remove rows that were dropped from the form
            $this->movements()->where('movement_type', $type)->whereNotIn('id', $keptIds)->get()
                ->each(fn (StockMovement $movement) => $this->deleteStock($movement));
        });
    }

    /**
     * Sync stock items from a single validated data array
     *
     * Expected format:
     * [
     *    'brand_id' => [1, 2, 3],
     *    'cylinder_type_id' => [1, 2, 3],
     *    'quantity' => [10, 5, 2]
     * ]
     */
    public function syncStockFromData(array $data, ?StockMovementType $type = null): void
    {
        // Basic validation
        if (
            empty($data['brand_id']) || ! is_array($data['brand_id']) ||
            empty($data['cylinder_type_id']) || ! is_array($data['cylinder_type_id']) ||
            empty($data['quantity']) || ! is_array($data['quantity'])
        ) {
            throw new \InvalidArgumentException('Invalid stock data structure.');
        }

        // Ensure all arrays have the same length
        $count = count($data['brand_id']);
        if ($count !== count($data['cylinder_type_id']) || $count !== count($data['quantity'])) {
            throw new \InvalidArgumentException('Brand, type, and quantity arrays must have the same number of elements.');
        }

        // Prepare items array
        $items = [];
        foreach ($data['brand_id'] as $index => $brandId) {
            $cylinderTypeId = $data['cylinder_type_id'][$index] ?? null;
            $quantity = $data['quantity'][$index] ?? null;

            if (! $brandId || ! $cylinderTypeId || ! $quantity) {
                continue;
            }

            $items[] = [
                'brand_id' => $brandId,
                'cylinder_type_id' => $cylinderTypeId,
                'quantity' => $quantity,
            ];
        }

        // Call the main sync method
        $this->syncStockItems($items, $type);
    }

    /**
     * Apply the movement type's sign (adjustments keep the sign they were given)
     */
    protected function signedQuantity(StockMovementType $type, int|string $quantity): int
    {
        $sign = $type->sign();

        return $sign === null ? (int) $quantity : abs((int) $quantity) * $sign;
    }

    protected function ensureOwnMovement(StockMovement $movement): void
    {
        if ($movement->source_type !== get_class($this) || $movement->source_id !== $this->getKey()) {
            throw new Exception('This stock movement does not belong to this model');
        }
    }
}
