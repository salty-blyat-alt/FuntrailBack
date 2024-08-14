<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            HotelsTableSeeder::class,
            RoomTypesTableSeeder::class,
            BookingsTableSeeder::class,
            HotelCommentsTableSeeder::class,
            HotelDetailsTableSeeder::class,
            OrdersTableSeeder::class,
            OrderDetailsTableSeeder::class,
            ProductsTableSeeder::class,
            RestaurantsTableSeeder::class,
            RestaurantCommentsTableSeeder::class,
        ]);
    }
}
