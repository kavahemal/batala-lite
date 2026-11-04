<?php

namespace Database\Factories;

use App\Models\Driver;
use Database\Factories\Concerns\LocalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
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
            'name' => $this->personName('male'),
            'mobile' => $this->freeMobile(),
        ];
    }

    public function withoutMobile(): static
    {
        return $this->state(['mobile' => null]);
    }

    protected function freeMobile(): string
    {
        do {
            $mobile = $this->mobileNumber();
        } while (Driver::withTrashed()->where('mobile', $mobile)->exists());

        return $mobile;
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
