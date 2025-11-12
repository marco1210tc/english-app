<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => "Tema: " . fake()->words(2, true),
            'icon_url' => fake()->imageUrl(64, 64, 'abstract'),
        ];
    }
}
