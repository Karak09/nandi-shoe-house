<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictMasterSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'West Bengal' => [
                'Kolkata','Howrah','Hooghly','North 24 Parganas',
                'South 24 Parganas','Nadia','Murshidabad',
                'Malda','Birbhum','Bankura','Purulia',
                'Paschim Medinipur','Purba Medinipur',
                'Jalpaiguri','Darjeeling'
            ],

            'Bihar' => [
                'Patna','Gaya','Muzaffarpur','Bhagalpur',
                'Purnia','Darbhanga','Nalanda','Bhojpur',
                'Begusarai','Samastipur','Katihar',
                'Munger','Rohtas','Siwan','Saran'
            ],

            'Jharkhand' => [
                'Ranchi','Dhanbad','Jamshedpur','Bokaro',
                'Deoghar','Hazaribagh','Giridih',
                'Palamu','Chatra','Dumka',
                'Godda','Koderma','Ramgarh',
                'Khunti','Latehar'
            ],

            'Odisha' => [
                'Bhubaneswar','Cuttack','Puri','Balasore',
                'Sambalpur','Jharsuguda','Koraput',
                'Ganjam','Mayurbhanj','Kendrapara',
                'Jagatsinghpur','Dhenkanal',
                'Kalahandi','Rayagada','Nabarangpur'
            ],

            'Assam' => [
                'Kamrup','Kamrup Metro','Nagaon','Jorhat',
                'Dibrugarh','Tinsukia','Sivasagar',
                'Barpeta','Goalpara','Cachar',
                'Karimganj','Dhemaji',
                'Lakhimpur','Sonitpur','Morigaon'
            ],

            'Uttar Pradesh' => [
                'Lucknow','Kanpur','Varanasi','Prayagraj',
                'Agra','Meerut','Gorakhpur',
                'Jhansi','Bareilly','Noida',
                'Mathura','Aligarh',
                'Ayodhya','Sultanpur','Azamgarh'
            ],

            'Maharashtra' => [
                'Mumbai','Pune','Nagpur','Nashik',
                'Thane','Aurangabad','Kolhapur',
                'Satara','Solapur','Amravati',
                'Akola','Latur',
                'Ratnagiri','Sindhudurg','Jalgaon'
            ]
        ];

        foreach ($districts as $stateName => $list) {

            $stateId = DB::table('state_masters')
                ->where('name', $stateName)
                ->value('id');

            foreach ($list as $district) {

                DB::table('district_masters')->insert([
                    'name' => $district,
                    'state_id' => $stateId,
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}