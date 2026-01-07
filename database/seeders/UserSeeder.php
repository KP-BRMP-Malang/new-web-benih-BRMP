<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if users already exist to avoid duplicate key errors
        if (!User::where('email', 'admin@test.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('Aa123456.'),
                'phone' => null,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'role' => 'admin'
            ]);
        }

        if (!User::where('email', 'user@test.com')->exists()) {
            User::create([
                'name' => 'User',
                'email' => 'user@test.com',
                'password' => Hash::make('Aa123456.'),
                'phone'=> '0812345678',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'role' => 'customer'
            ]);
        }
    }
}
