<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Activity;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Asume que se proveerán al crearlo, o crea nuevos
            'teacher_id' => User::factory()->teacher(),
            'classroom_id' => Classroom::factory(),
            'activity_id' => Activity::factory(),
            'title' => "Tarea: " . fake()->sentence(2),
            'due_date' => fake()->dateTimeBetween('+1 week', '+4 weeks'),
        ];
    }
}
