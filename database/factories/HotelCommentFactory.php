<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelComment>
 */
class HotelCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'context' => $this->faker->text,
            'star' => $this->faker->numberBetween(1, 5),
            'user_id' => \App\Models\User::factory(),
            'hotel_id' => \App\Models\Hotel::factory(),
        ];
    }
}
