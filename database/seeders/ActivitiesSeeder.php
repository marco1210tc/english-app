<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = DB::table('lessons')->get(['id']);

        $listeningTypeId = DB::table('item_types')->where('key', 'listening')->value('id');
        $matchingTypeId  = DB::table('item_types')->where('key', 'matching')->value('id');
        $orderingTypeId  = DB::table('item_types')->where('key', 'ordering')->value('id');
        $mcqTypeId       = DB::table('item_types')->where('key', 'multiple_choice')->value('id');

        if (!$listeningTypeId || !$matchingTypeId || !$orderingTypeId || !$mcqTypeId) {
            throw new \RuntimeException("Faltan item_types. Ejecuta ItemTypesSeeder o revisa keys.");
        }

        foreach ($lessons as $lesson) {
            $vocabIds = DB::table('lesson_vocabulary')
                ->where('lesson_id', $lesson->id)
                ->orderBy('order_index')
                ->pluck('vocabulary_id')
                ->all();

            if (count($vocabIds) === 0) continue;

            $wordsMap = DB::table('vocabulary')->whereIn('id', $vocabIds)->pluck('word_en', 'id');

            // Listen & Choose
            $listenId = DB::table('activities')->insertGetId([
                'lesson_id' => $lesson->id,
                'title' => 'Listen & Choose',
                'description' => 'Escucha el audio y elige la imagen/palabra correcta',
                'item_type_id' => $listeningTypeId,
                'difficulty' => 'easy',
                'max_score' => count($vocabIds),
                'order_index' => 0,
                'is_active' => true,
                'config_json' => json_encode(['vocabulary_ids' => $vocabIds]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Match
            $matchId = DB::table('activities')->insertGetId([
                'lesson_id' => $lesson->id,
                'title' => 'Match',
                'description' => 'Empareja imagen con palabra',
                'item_type_id' => $matchingTypeId,
                'difficulty' => 'easy',
                'max_score' => count($vocabIds),
                'order_index' => 1,
                'is_active' => true,
                'config_json' => json_encode(['vocabulary_ids' => $vocabIds]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Order Letters
            $orderId = DB::table('activities')->insertGetId([
                'lesson_id' => $lesson->id,
                'title' => 'Order Letters',
                'description' => 'Ordena las letras para formar la palabra',
                'item_type_id' => $orderingTypeId,
                'difficulty' => 'easy',
                'max_score' => count($vocabIds),
                'order_index' => 2,
                'is_active' => true,
                'config_json' => json_encode(['vocabulary_ids' => $vocabIds]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mini-quiz (5 preguntas o menos)
            $quizVocab = array_slice($vocabIds, 0, min(5, count($vocabIds)));
            $quizId = DB::table('activities')->insertGetId([
                'lesson_id' => $lesson->id,
                'title' => 'Mini Quiz',
                'description' => 'Preguntas de opción múltiple',
                'item_type_id' => $mcqTypeId,
                'difficulty' => 'easy',
                'max_score' => count($quizVocab),
                'order_index' => 3,
                'is_active' => true,
                'config_json' => json_encode(['vocabulary_ids' => $quizVocab]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear preguntas/opciones para el quiz
            foreach ($quizVocab as $qIndex => $vocabId) {
                $questionId = DB::table('questions')->insertGetId([
                    'activity_id' => $quizId,
                    'vocabulary_id' => $vocabId,
                    'prompt' => 'Choose the correct answer',
                    'image_path' => null,
                    'audio_path' => null,
                    'order_index' => $qIndex,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Opciones (1 correcta + 3 distractores)
                $pool = $vocabIds;
                shuffle($pool);

                $options = array_values(array_unique(array_merge([$vocabId], array_slice($pool, 0, 3))));
                $options = array_slice($options, 0, 4);
                shuffle($options);

                foreach ($options as $oIndex => $optVocabId) {

                    $word = $wordsMap[$optVocabId] ?? null;

                    DB::table('question_options')->insert([
                        'question_id' => $questionId,
                        'text' => $word,
                        'image_path' => null,
                        'is_correct' => ($optVocabId === $vocabId),
                        'order_index' => $oIndex,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
