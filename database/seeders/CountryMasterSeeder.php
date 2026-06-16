<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('country_masters')->insert([
            [
                'id' => 1,
                'c_name' => 'India',
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}