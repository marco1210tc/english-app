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
            ['key' => 'vocabulary',     'name' => 'Vocabulary',       'description' => 'Reconocimiento de vocabulario', 'is_active' => true],
            ['key' => 'quiz_question',  'name' => 'Quiz (MCQ)',       'description' => 'Pregunta con opciones',        'is_active' => true],
            ['key' => 'listen_choose',  'name' => 'Listen & Choose',  'description' => 'Escucha y elige',             'is_active' => true],
            ['key' => 'match',          'name' => 'Match',            'description' => 'Emparejar imagen-palabra',    'is_active' => true],
            ['key' => 'order_letters',  'name' => 'Order Letters',    'description' => 'Ordenar letras',              'is_active' => true],
        ];

        foreach ($types as $t) {
            DB::table('item_types')->updateOrInsert(
                ['key' => $t['key']],
                array_merge($t, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
