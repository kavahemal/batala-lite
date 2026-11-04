<?php

namespace Database\Factories;

use App\Models\Customer;
use Database\Factories\Concerns\LocalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'name' => $this->personName(),
            'mobile_number' => $this->freeMobile(),
            'address' => $this->localAddress(),
            'is_active' => true,
        ];
    }

    /**
     * A mobile no customer has yet (app:fake-data adds on top of existing customers)
     */
    protected function freeMobile(): string
    {
        do {
            $mobile = fake()->unique()->numerify('99999#####'); // obviously fake, same as LocalData::mobileNumber()
        } while (Customer::where('mobile_number', $mobile)->exists());

        return $mobile;
    }

    /**
     * Indicate that the customer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
