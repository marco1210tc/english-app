<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Crea un nuevo maestro para esta clase
            'teacher_id' => User::factory()->teacher(), 
            'name' => "Grado " . fake()->numberBetween(1, 3) . " - " . fake()->randomLetter(),
            'grade_level' => fake()->numberBetween(1, 3),
        ];
    }
}
