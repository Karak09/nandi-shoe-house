<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin',
            'Sales Manager',
            'Reporter',
            'Account',
            'Purchase Entry',
            'Stock Transfer'
        ];

        foreach ($roles as $role) {
            DB::table('user_type_masters')->insert([
                'u_type' => $role,
                'is_active' => true,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}