<?php

namespace Database\Factories;

use App\Models\User;
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
        $user = User::inRandomOrder()->first();
        return [
            'name' => $this->faker->company,
            'province' => $this->faker->state,
            'user_id' => $user ? $user->id : null, 
            'address' => $this->faker->address,
            'description' => $this->faker->paragraph,
            'phone_number' => $this->faker->phoneNumber,
            'open_at' => $this->faker->time,
            'close_at' => $this->faker->time,
            'image' => $this->faker->imageUrl(640, 480, 'restaurant', true),
        ];
    }
}
