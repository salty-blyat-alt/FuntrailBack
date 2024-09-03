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
            'password' => bcrypt('password'),  
            'remember_token' => $this->faker->optional()->word,
            'user_type' => $this->faker->randomElement(['customer', 'hotel', 'restaurant']), // Ensure only valid types
            'province_id' => $province ? $province->id : 1,
            'balance' => $this->faker->randomFloat(2, 0, 1000),  
            'phone_number' => $this->faker->optional()->phoneNumber(), 
            'profile_img' => null, 
        ];
    }
}
