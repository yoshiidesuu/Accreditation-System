<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Check if admin user already exists
        $existingAdmin = DB::table('users')->where('employee_id', 'admin')->first();
        
        if ($existingAdmin) {
            // Update existing admin user
            DB::table('users')
                ->where('employee_id', 'admin')
                ->update([
                    'password' => Hash::make('admin'),
                    'updated_at' => $now,
                ]);
            
            $this->command->info('Admin user updated successfully.');
        } else {
            // Create new admin user
            DB::table('users')->insert([
                'employee_id' => 'admin',
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'Administrator',
                'email' => 'admin@earist.edu.ph',
                'email_verified_at' => $now,
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'status' => 'active',
                'department' => 'Administration',
                'position' => 'System Administrator',
                'phone' => '+63-123-456-7890',
                'permissions' => json_encode([
                    'manage_users',
                    'manage_parameters',
                    'manage_evaluations',
                    'generate_reports',
                    'system_settings',
                    'view_all_data'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('Admin user created successfully.');
        }
    }
}