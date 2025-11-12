<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Activity;

class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'title' => "Reto: " . fake()->sentence(3),
            'points_reward' => fake()->randomElement([50, 100, 150, 200]),
            'is_active' => true,
        ];
    }
}
