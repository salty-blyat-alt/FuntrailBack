<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $province = Province::inRandomOrder()->first();
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->faker->optional()->dateTime(),
            'password' => bcrypt('password'), // Use bcrypt for hashed password
            'remember_token' => $this->faker->optional()->word,
            'user_type' => $this->faker->randomElement(['customer', 'hotel', 'restaurant']), // Ensure only valid types
            'province_id' => $province ? $province->id : null,// Generates a random existing province ID
            'balance' => $this->faker->randomFloat(2, 0, 1000), // Ensure decimal value with 2 decimal places
            'phone_number' => $this->faker->optional()->phoneNumber(), // Nullable
            'profile_img' => $this->faker->optional()->imageUrl(), // Nullable
        ];
    }
}
