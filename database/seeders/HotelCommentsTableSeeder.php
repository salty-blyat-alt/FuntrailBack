<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelComment;

class HotelCommentsTableSeeder extends Seeder
{
    public function run()
    {
        HotelComment::factory()->count(50)->create();
    }
}
