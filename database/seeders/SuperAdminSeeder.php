<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Users\User;
use App\Models\Users\UsersDetails;
use App\Models\Users\UserTypeMaster;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily so the seeder doesn't fail 
        // if your State or District master tables are currently empty.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::beginTransaction();

        try {
            // 1. Create the 'Super Admin' Role
            $superAdminRole = UserTypeMaster::firstOrCreate(
                ['u_type' => 'Super Admin'],
                ['is_active' => true, 'is_deleted' => false]
            );

            // 2. Create the Super Admin Profile
            $adminDetails = UsersDetails::updateOrCreate(
                ['email' => 'superadmin@gmail.com'], // Look for this email
                [
                    'f_name'           => 'System',
                    'l_name'           => 'Administrator',
                    'user_name'        => 'superadmin',
                    'mobile'           => '1234567890', // Default Super Admin Mobile
                    'vrfy_mobile'      => true,         // Pre-verified
                    'vrfy_email'       => true,         // Pre-verified
                    'verify_status_id' => 1,            // 1 = Auto Approved!
                    'is_active'        => true,
                    'is_deleted'       => false,
                    'state_id'         => 1,            // Dummy ID
                    'district_id'      => 1,            // Dummy ID
                    'date_of_reg'      => now(),
                ]
            );

            // 3. Create the Super Admin Login Credentials
            $comPassword = '1234567890';

            User::updateOrCreate(
                ['login_id' => 'superadmin@gmail.com'], // Login ID
                [
                    'user_details_id' => $adminDetails->id,
                    'user_type_id'    => $superAdminRole->id,
                    'username'        => 'superadmin',
                    'com_password'    => $comPassword,
                    'password'        => Hash::make($comPassword),
                    'is_active'       => true,
                    'is_deleted'      => false,
                    'entry_time'      => now(),
                ]
            );

            DB::commit();
            $this->command->info('✅ Super Admin seeded successfully!');
            $this->command->info('📧 Login ID: superadmin@gmail.com');
            $this->command->info('🔑 Password: ' . $comPassword);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed Super Admin: ' . $e->getMessage());
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}