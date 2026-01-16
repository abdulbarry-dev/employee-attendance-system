<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create employee role
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    }
}
