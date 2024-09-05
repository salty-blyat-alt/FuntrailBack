<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Session;

class SessionsTableSeeder extends Seeder
{
    public function run()
    {
        Session::factory()->count(10)->create();
    }
}
