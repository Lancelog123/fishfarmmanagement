<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure only one admin exists
        User::updateOrCreate(
            ['username' => 'admin'], // condition (unique)
            [
                'fullname' => 'System Administrator',
                'email' => 'admin@example.com',
                'phone' => '09123456789',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'approved'
            ]
        );
    }
}
