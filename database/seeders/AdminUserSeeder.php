<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@arena.com'],
            [
                'name' => 'Administrator',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_banned' => false,
            ]
        );

        // Assign admin role
        $admin->assignRole('admin');
    }
}
