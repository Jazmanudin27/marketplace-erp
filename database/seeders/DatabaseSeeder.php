<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Create or get test company
        $company = \App\Models\Company::firstOrCreate(
            ['slug' => 'test-company'],
            [
                'name' => 'Test Company',
                'email' => 'company@test.com',
                'phone' => '081234567890',
                'status' => 'active',
            ]
        );

        // Get roles
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $managerRole = \App\Models\Role::where('name', 'manager')->first();
        $staffRole = \App\Models\Role::where('name', 'staff')->first();

        // Create admin user if not exists
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'company_id' => $company->id,
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );
        if (!$adminUser->companies()->where('company_id', $company->id)->exists()) {
            $adminUser->companies()->attach($company->id, [
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]);
        }

        // Create manager user if not exists
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'Manager User',
                'company_id' => $company->id,
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );
        if (!$managerUser->companies()->where('company_id', $company->id)->exists()) {
            $managerUser->companies()->attach($company->id, [
                'role_id' => $managerRole->id,
                'status' => 'active',
            ]);
        }

        // Create staff user if not exists
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => 'Staff User',
                'company_id' => $company->id,
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );
        if (!$staffUser->companies()->where('company_id', $company->id)->exists()) {
            $staffUser->companies()->attach($company->id, [
                'role_id' => $staffRole->id,
                'status' => 'active',
            ]);
        }
    }
}
