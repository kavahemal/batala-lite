<?php

namespace Database\Factories;

use App\Models\CylinderType;
use App\Models\Driver;
use App\Models\Purchase;
use App\Models\Truck;
use Database\Factories\Concerns\LocalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    use LocalData;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'truck_id' => fn () => (fake()->boolean(70) ? Truck::active()->inRandomOrder()->value('id') : null) ?? Truck::factory()->create()->id,
            'driver_id' => fn () => (fake()->boolean(70) ? Driver::active()->inRandomOrder()->value('id') : null) ?? Driver::factory()->create()->id,
            'purchase_date' => fake()->dateTimeBetween('-14 days', 'now'),
            'remarks' => $this->purchaseRemark(),
        ];
    }

    /**
     * Add 1-4 cylinder items (stock movements) after creating.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Purchase $purchase) {
            CylinderType::active()->inRandomOrder()->take(fake()->numberBetween(1, 4))->get()
                ->each(fn (CylinderType $type) => $purchase->addStock([
                    'brand_id' => $type->brand_id,
                    'cylinder_type_id' => $type->id,
                    'quantity' => fake()->numberBetween(10, 100),
                ]));
        });
    }
}
