<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Enver',
            'cust_code' => 'CUST00006',
        ]);

        Customer::create([
            'name' => 'Valdo',
            'cust_code' => 'CUST00007',
        ]);

        Customer::create([
            'name' => 'Wiwit',
            'cust_code' => 'CUST00008',
        ]);

    }
}
