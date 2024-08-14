<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelDetail;

class HotelDetailsTableSeeder extends Seeder
{
    public function run()
    {
        HotelDetail::factory()->count(50)->create();
    }
}
