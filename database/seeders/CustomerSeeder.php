<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 nasabah users first, then link them to customers
        $nasabah1 = User::create([
            'name' => 'Enver',
            'email' => 'enver@mail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'nasabah',
        ]);

        $nasabah2 = User::create([
            'name' => 'Valdo',
            'email' => 'valdo@mail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'nasabah',
        ]);

        $nasabah3 = User::create([
            'name' => 'Wiwit',
            'email' => 'wiwit@mail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'nasabah',
        ]);

        // Create customers linked to users
        Customer::create([
            'user_id' => $nasabah1->id,
            'name' => 'Enver',
            'cust_code' => 'CUST00006',
            'photo' => null,
        ]);

        Customer::create([
            'user_id' => $nasabah2->id,
            'name' => 'Valdo',
            'cust_code' => 'CUST00007',
            'photo' => null,
        ]);

        Customer::create([
            'user_id' => $nasabah3->id,
            'name' => 'Wiwit',
            'cust_code' => 'CUST00008',
            'photo' => null,
        ]);
    }
}
