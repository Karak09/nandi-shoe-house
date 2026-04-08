<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('123456'),
        ]);
    }
}