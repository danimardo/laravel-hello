<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('username', 'admin')
            ->orWhere('email', 'admin@example.com')
            ->exists();

        if (!$adminExists) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin12345*'), // Default password - should be changed
                'role' => 'admin',
                'status' => 'active',
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Username: admin');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: Admin12345*');
            $this->command->warn('Please change the default password after first login!');
        } else {
            $this->command->info('Admin user already exists!');
        }
    }
}
