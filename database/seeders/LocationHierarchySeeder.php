<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $districts = DB::table('district_masters')->get();

        foreach ($districts as $district) {

            // Blocks
            for ($i = 1; $i <= 10; $i++) {

                $blockId = DB::table('block_masters')->insertGetId([
                    'name' => $district->name . ' Block ' . $i,
                    'district_id' => $district->id,
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Post Offices
                for ($po = 1; $po <= 15; $po++) {

                    DB::table('post_office_masters')->insert([
                        'name' => 'PO ' . $po . ' - Block ' . $i,
                        'block_id' => $blockId,
                        'is_active' => 1,
                        'is_deleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Gram Panchayat
                for ($gp = 1; $gp <= 12; $gp++) {

                    $gpId = DB::table('gram_panchayat_masters')->insertGetId([
                        'name' => 'GP ' . $gp . ' - Block ' . $i,
                        'block_id' => $blockId,
                        'is_active' => 1,
                        'is_delete' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Villages
                    for ($v = 1; $v <= 15; $v++) {

                        DB::table('village_masters')->insert([
                            'name' => 'Village ' . $v . ' - GP ' . $gp,
                            'gram_panchayat_id' => $gpId,
                            'is_active' => 1,
                            'is_delete' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Municipality
            for ($m = 1; $m <= 12; $m++) {

                $municipalityId = DB::table('municipality_masters')
                    ->insertGetId([
                        'name' => $district->name . ' Municipality ' . $m,
                        'type' => 'Municipality',
                        'district_id' => $district->id,
                        'is_active' => 1,
                        'is_delete' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                // Wards
                for ($w = 1; $w <= 15; $w++) {

                    DB::table('ward_masters')->insert([
                        'name' => 'Ward ' . $w,
                        'municipality_id' => $municipalityId,
                        'is_active' => 1,
                        'is_delete' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}