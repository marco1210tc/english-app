<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['key' => 'multiple_choice', 'name' => 'Multiple Choice', 'description' => 'Opción múltiple', 'is_active' => true],
            ['key' => 'true_false',      'name' => 'True/False',      'description' => 'Verdadero/Falso', 'is_active' => true],
            ['key' => 'matching',        'name' => 'Matching',        'description' => 'Emparejar', 'is_active' => true],
            ['key' => 'listening',       'name' => 'Listening',       'description' => 'Escucha y elige', 'is_active' => true],
            ['key' => 'ordering',        'name' => 'Ordering',        'description' => 'Ordenar', 'is_active' => true],
            ['key' => 'drag_drop',       'name' => 'Drag & Drop',     'description' => 'Arrastrar y soltar', 'is_active' => true],
            ['key' => 'memory_cards',    'name' => 'Memory Cards',    'description' => 'Memoria', 'is_active' => true],

            // útil para tests si metes ítems no-activity:
            ['key' => 'vocabulary',      'name' => 'Vocabulary',      'description' => 'Reconocimiento de vocabulario', 'is_active' => true],
        ];

        foreach ($types as $t) {
            DB::table('item_types')->updateOrInsert(
                ['key' => $t['key']],
                array_merge($t, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
