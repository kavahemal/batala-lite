<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CylinderType;
use Database\Factories\Concerns\LocalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    use LocalData;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = fake()->dateTimeBetween('-14 days', 'now');

        return [
            'customer_id' => Customer::active()->inRandomOrder()->value('id') ?? Customer::factory(),
            'status' => BookingStatus::PENDING,
            'booking_date' => $bookingDate,
            'scheduled_date' => fake()->optional()->dateTimeBetween($bookingDate, '+7 days'),
            'remarks' => $this->bookingRemark(),
        ];
    }

    /**
     * Add 1-3 cylinder items (stock movements) after creating.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Booking $booking) {
            CylinderType::active()->inRandomOrder()->take(fake()->numberBetween(1, 3))->get()
                ->each(fn (CylinderType $type) => $booking->addStock([
                    'brand_id' => $type->brand_id,
                    'cylinder_type_id' => $type->id,
                    'quantity' => fake()->numberBetween(1, 5),
                ]));
        });
    }

    /**
     * Set the booking status.
     */
    public function status(BookingStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
