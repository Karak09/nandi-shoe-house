<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\Users\User;
use App\Models\Users\UsersDetails;
use App\Models\Users\UserTypeMaster;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::beginTransaction();

        try {

            $defaultPassword = '1234567890';

            $users = [

                [
                    'role'      => 'Admin',
                    'username'  => 'admin',
                    'email'     => 'admin@gmail.com',
                    'first'     => 'Admin',
                    'last'      => 'User',
                    'mobile'    => '1111111111',
                ],

                [
                    'role'      => 'Sales Manager',
                    'username'  => 'salesmanager',
                    'email'     => 'salesmanager@gmail.com',
                    'first'     => 'Sales',
                    'last'      => 'Manager',
                    'mobile'    => '2222222222',
                ],

                [
                    'role'      => 'Store Manager',
                    'username'  => 'storemanager',
                    'email'     => 'storemanager@gmail.com',
                    'first'     => 'Store',
                    'last'      => 'Manager',
                    'mobile'    => '3333333333',
                ],

                [
                    'role'      => 'Account',
                    'username'  => 'account',
                    'email'     => 'account@gmail.com',
                    'first'     => 'Account',
                    'last'      => 'User',
                    'mobile'    => '4444444444',
                ],

                [
                    'role'      => 'Purchase Entry',
                    'username'  => 'purchase',
                    'email'     => 'purchase@gmail.com',
                    'first'     => 'Purchase',
                    'last'      => 'Entry',
                    'mobile'    => '5555555555',
                ],

                [
                    'role'      => 'Stock Transfer',
                    'username'  => 'stocktransfer',
                    'email'     => 'stocktransfer@gmail.com',
                    'first'     => 'Stock',
                    'last'      => 'Transfer',
                    'mobile'    => '6666666666',
                ],

                [
                    'role'      => '3rd party',
                    'username'  => 'thirdparty',
                    'email'     => 'thirdparty@gmail.com',
                    'first'     => 'Third',
                    'last'      => 'Party',
                    'mobile'    => '7777777777',
                ],

            ];

            foreach ($users as $u) {

                // Find role
                $role = UserTypeMaster::where('u_type', $u['role'])->first();

                if (!$role) {
                    continue;
                }

                // Create profile
                $details = UsersDetails::updateOrCreate(

                    ['email' => $u['email']],

                    [
                        'f_name'           => $u['first'],
                        'l_name'           => $u['last'],
                        'user_name'        => $u['username'],
                        'mobile'           => $u['mobile'],

                        'vrfy_mobile'      => true,
                        'vrfy_email'       => true,

                        'verify_status_id' => 1,

                        'is_active'        => true,
                        'is_deleted'       => false,

                        'state_id'         => 1,
                        'district_id'      => 1,

                        'date_of_reg'      => now(),
                    ]
                );

                // Create login
                User::updateOrCreate(

                    ['login_id' => $u['email']],

                    [
                        'user_details_id' => $details->id,
                        'user_type_id'    => $role->id,

                        'username'        => $u['username'],

                        'com_password'    => $defaultPassword,

                        'password'        => Hash::make($defaultPassword),

                        'is_active'       => true,
                        'is_deleted'      => false,

                        'entry_time'      => now(),
                    ]
                );
            }

            DB::commit();

            $this->command->info('Default users seeded successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            $this->command->error($e->getMessage());
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}