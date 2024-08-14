<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RestaurantComment;

class RestaurantCommentsTableSeeder extends Seeder
{
    public function run()
    {
        RestaurantComment::factory()->count(50)->create();
    }
}
