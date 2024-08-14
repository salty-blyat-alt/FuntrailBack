<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PasswordResetToken;

class PasswordResetTokensTableSeeder extends Seeder
{
    public function run()
    {
        PasswordResetToken::factory()->count(50)->create();
    }
}
