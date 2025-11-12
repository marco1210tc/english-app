<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . " Master",
            'description' => fake()->sentence(),
            'icon_url' => fake()->imageUrl(64, 64, 'badge'),
        ];
    }
}
