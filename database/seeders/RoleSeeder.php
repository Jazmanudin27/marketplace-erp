<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access to all features and user management',
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Can manage marketplace accounts and view reports',
            ],
            [
                'name' => 'staff',
                'display_name' => 'Staff',
                'description' => 'Can view marketplace accounts and orders',
            ],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
