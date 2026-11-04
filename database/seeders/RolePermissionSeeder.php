<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get existing roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $subOwner = Role::firstOrCreate(['name' => 'sub owner']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Create users and assign roles
        // Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => '12345678',
            ]
        );
        $adminUser->assignRole($admin);

        // Owner User
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'password' => '12345678',
            ]
        );
        $ownerUser->assignRole($owner);

        $subOwnerUser = User::firstOrCreate(
            ['email' => 'subowner@example.com'],
            [
                'name' => 'Sub User',
                'password' => '12345678',
            ]
        );
        $subOwnerUser->assignRole($subOwner);

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff 1',
                'password' => '12345678',
            ]
        );
        $staffUser->assignRole($staff);
    }
}
