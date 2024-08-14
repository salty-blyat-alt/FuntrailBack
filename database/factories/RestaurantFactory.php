<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'province' => $this->faker->state,
            'address' => $this->faker->address,
            'description' => $this->faker->paragraph,
            'phone_number' => $this->faker->phoneNumber,
            'open_at' => $this->faker->time,
            'close_at' => $this->faker->time,
        ];
    }
}
