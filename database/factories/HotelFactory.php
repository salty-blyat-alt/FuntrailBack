<?php

namespace Database\Factories;

use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $province = Province::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();
        return [
            'name' => $this->faker->company,
            'user_id' => $user ? $user->id : null,  
            'province_id' => $province ? $province->id : 1,           
            'address' => $this->faker->address,
            'description' => $this->faker->paragraph,
            'room_available' => $this->faker->numberBetween(0, 100),
            'phone_number' => $this->faker->phoneNumber,
            'open_at' => $this->faker->time,
            'close_at' => $this->faker->time,
            'images' => json_encode(array_map(function() {
                return $this->faker->imageUrl(640, 480, 'hotel', true);
            }, range(1, 5))),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'hotel', true),  
        ];
    }
}
