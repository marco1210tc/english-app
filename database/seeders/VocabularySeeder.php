<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VocabularySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin@englishapp.test')->value('id');

        $words = [
            ['word_en' => 'cat', 'translation_es' => 'gato'],
            ['word_en' => 'dog', 'translation_es' => 'perro'],
            ['word_en' => 'apple', 'translation_es' => 'manzana'],
            ['word_en' => 'banana', 'translation_es' => 'plátano'],
            ['word_en' => 'red', 'translation_es' => 'rojo'],
            ['word_en' => 'blue', 'translation_es' => 'azul'],
            ['word_en' => 'one', 'translation_es' => 'uno'],
            ['word_en' => 'two', 'translation_es' => 'dos'],
            ['word_en' => 'car', 'translation_es' => 'carro'],
            ['word_en' => 'house', 'translation_es' => 'casa'],
        ];

        foreach ($words as $w) {
            DB::table('vocabulary')->updateOrInsert(
                ['word_en' => $w['word_en']],
                [
                    'translation_es' => $w['translation_es'],
                    'image_path' => null,
                    'audio_path' => null,
                    'status' => 'published',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
