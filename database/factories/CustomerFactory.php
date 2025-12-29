<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'photo' => null,
            'profile' => $this->faker->paragraph(),
            'rekomendasi' => $this->faker->randomElement(['Tabungan A', 'Deposito B', 'KPR C']),
        ];
    }
}
