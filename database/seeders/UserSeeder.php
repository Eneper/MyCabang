<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create security officer and teller users only
        // Nasabah users will be created by CustomerSeeder
        User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'security',
        ]);

        User::create([
            'name' => 'Teller',
            'email' => 'teller@mail.com',
            'password' => Hash::make('password'),
            'role' => 'teller',
        ]);
    }
}
