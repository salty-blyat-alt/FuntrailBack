<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelDetail>
 */
class HotelDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => \App\Models\RoomType::factory(),
            'hotel_id' => \App\Models\Hotel::factory(),
            'is_available' => $this->faker->boolean,
        ];
    }
}
