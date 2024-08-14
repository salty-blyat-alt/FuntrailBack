<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypesTableSeeder extends Seeder
{
    public function run()
    {
        RoomType::factory()->count(10)->create();
    }
}
