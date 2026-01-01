<?php

namespace Database\Seeders;

use Database\Seeders\UserSeeder;
use Database\Seeders\CustomerSeeder;
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
        // Seed users and customers used by teller dashboard
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
