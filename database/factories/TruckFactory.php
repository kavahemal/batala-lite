<?php

namespace Database\Factories;

use App\Models\Truck;
use Database\Factories\Concerns\LocalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Truck>
 */
class TruckFactory extends Factory
{
    use LocalData;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {
            $truckNo = $this->localTruckNo();
        } while (Truck::withTrashed()->where('truck_no', $truckNo)->exists());

        return ['truck_no' => $truckNo];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
