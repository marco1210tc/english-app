<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Topic;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Asigna un Topic existente o crea uno nuevo
            'topic_id' => Topic::factory(), 
            'name' => "Actividad: " . fake()->sentence(3),
            'description' => fake()->paragraph(1),
            
            // --- Por defecto, creamos un Quiz ---
            'type' => 'quiz',
            'content' => $this->getQuizContent(),
        ];
    }

    // --- ESTADO para Quiz (¡El que faltaba!) ---
    public function quiz(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
            'content' => $this->getQuizContent(),
        ]);
    }

    // --- ESTADO para Juego de Memoria ---
    public function memoryGame(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'memory_game',
            'content' => $this->getMemoryGameContent(),
        ]);
    }

    // --- ESTADO para Arrastrar y Soltar ---
    public function dragAndDrop(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'drag_and_drop_match',
            'content' => $this->getDragDropContent(),
        ]);
    }

    // --- Funciones privadas para generar el JSON ---

    private function getQuizContent(): array
    {
        return [
          'questions' => [
            ['text' => '¿Cuál es "Rojo" en inglés?', 'options' => [
                ['text' => 'Blue', 'is_correct' => false],
                ['text' => 'Red', 'is_correct' => true],
            ]],
            ['text' => '¿Cuál es "Perro"?', 'options' => [
                ['text' => 'Dog', 'is_correct' => true],
                ['text' => 'Cat', 'is_correct' => false],
            ]],
          ]
        ];
    }

    private function getMemoryGameContent(): array
    {
        return [
          'pairs' => [
            ['id' => 1, 'image' => fake()->imageUrl(100, 100, 'animals'), 'text' => 'Dog'],
            ['id' => 2, 'image' => fake()->imageUrl(100, 100, 'animals'), 'text' => 'Cat'],
            ['id' => 3, 'image' => fake()->imageUrl(100, 100, 'colors'), 'text' => 'Blue'],
          ]
        ];
    }
    
    private function getDragDropContent(): array
    {
        return [
            'draggables' => [
                ['id' => 'a', 'text' => 'Perro'],
                ['id' => 'b', 'text' => 'Pez'],
            ],
            'targets' => [
                ['id' => 't1', 'text' => 'Nadar'],
                ['id' => 't2', 'text' => 'Ladrar'],
            ],
            'correct_pairs' => [
                ['draggable_id' => 'a', 'target_id' => 't2'],
                ['draggable_id' => 'b', 'target_id' => 't1'],
            ]
        ];
    }
}
