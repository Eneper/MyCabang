<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FaceDetection;

class FaceDetectionFactory extends Factory
{
    protected $model = FaceDetection::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'photo' => null,
            'metadata' => ['camera' => fake()->word()],
            'customer_id' => null,
        ];
    }
}
