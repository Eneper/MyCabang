<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Queue>
 */
class QueueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => (string) $this->faker->unique()->numberBetween(1, 999),
            'status' => 'active',
            'note' => $this->faker->sentence(4),
            'served_at' => null,
        ];
    }
}
