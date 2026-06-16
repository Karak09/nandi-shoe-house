<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateMasterSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            'West Bengal',
            'Bihar',
            'Jharkhand',
            'Odisha',
            'Assam',
            'Uttar Pradesh',
            'Maharashtra'
        ];

        foreach ($states as $state) {
            DB::table('state_masters')->insert([
                'name' => $state,
                'country_id' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}