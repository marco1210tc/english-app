<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ✅ Traemos grade_id desde modules (porque lessons no lo tiene)
        $lessons = DB::table('lessons as l')
            ->join('modules as m', 'm.id', '=', 'l.module_id')
            ->get(['l.id as id', 'm.grade_id as grade_id']);

        $listeningTypeId = DB::table('item_types')->where('key', 'listening')->value('id');
        $matchingTypeId  = DB::table('item_types')->where('key', 'matching')->value('id');
        $orderingTypeId  = DB::table('item_types')->where('key', 'ordering')->value('id');
        $mcqTypeId       = DB::table('item_types')->where('key', 'multiple_choice')->value('id');

        $required = [
            'listening'       => $listeningTypeId,
            'matching'        => $matchingTypeId,
            'ordering'        => $orderingTypeId,
            'multiple_choice' => $mcqTypeId,
        ];

        foreach ($required as $key => $id) {
            if (!$id) {
                throw new \RuntimeException("Falta item_type '{$key}'. Ejecuta ItemTypesSeeder o revisa keys.");
            }
        }

        foreach ($lessons as $lesson) {

            $vocabIds = DB::table('lesson_vocabulary')
                ->where('lesson_id', $lesson->id)
                ->orderBy('order_index')
                ->pluck('vocabulary_id')
                ->all();

            if (count($vocabIds) === 0) continue;

            $itemsPerSession = min(5, count($vocabIds));

            $wordsMap = DB::table('vocabulary')
                ->whereIn('id', $vocabIds)
                ->pluck('word_en', 'id');

            $upsertActivity = function (int $lessonId, int $orderIndex, array $data) use ($now): int {
                DB::table('activities')->updateOrInsert(
                    ['lesson_id' => $lessonId, 'order_index' => $orderIndex],
                    array_merge($data, [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ])
                );

                return (int) DB::table('activities')
                    ->where('lesson_id', $lessonId)
                    ->where('order_index', $orderIndex)
                    ->value('id');
            };

            $wipeQuizQuestions = function (int $activityId): void {
                $qIds = DB::table('questions')
                    ->where('activity_id', $activityId)
                    ->pluck('id')
                    ->all();

                if (!empty($qIds)) {
                    DB::table('question_options')->whereIn('question_id', $qIds)->delete();
                }

                DB::table('questions')->where('activity_id', $activityId)->delete();
            };

            // 0) Listen & Choose
            $upsertActivity($lesson->id, 0, [
                'title'        => 'Listen & Choose',
                'description'  => 'Escucha el audio y elige la imagen/palabra correcta',
                'item_type_id' => $listeningTypeId,
                'difficulty'   => 'easy',
                'max_score'    => $itemsPerSession,
                'is_active'    => true,
                'config_json'  => json_encode([
                    'vocabulary_ids'    => $vocabIds,
                    'items_per_session' => $itemsPerSession,
                    'max_attempts'      => 3,
                ]),
            ]);

            // 1) Matching
            $upsertActivity($lesson->id, 1, [
                'title'        => 'Match',
                'description'  => 'Empareja imagen con palabra',
                'item_type_id' => $matchingTypeId,
                'difficulty'   => 'easy',
                'max_score'    => $itemsPerSession,
                'is_active'    => true,
                'config_json'  => json_encode([
                    'vocabulary_ids'    => $vocabIds,
                    'items_per_session' => $itemsPerSession,
                    'max_attempts'      => 3,
                ]),
            ]);

            // 2) Ordering solo 2° y 3°
            if (in_array((int)$lesson->grade_id, [2, 3], true)) {
                $upsertActivity($lesson->id, 2, [
                    'title'        => 'Order Letters',
                    'description'  => 'Ordena las letras para formar la palabra',
                    'item_type_id' => $orderingTypeId,
                    'difficulty'   => 'medium',
                    'max_score'    => $itemsPerSession,
                    'is_active'    => true,
                    'config_json'  => json_encode([
                        'vocabulary_ids'    => $vocabIds,
                        'items_per_session' => $itemsPerSession,
                        'max_attempts'      => 3,
                    ]),
                ]);
            } else {
                // Si existe el ordering para 1°, lo desactivamos
                DB::table('activities')
                    ->where('lesson_id', $lesson->id)
                    ->where('order_index', 2)
                    ->update(['is_active' => false, 'updated_at' => $now]);
            }

            // 3) Mini Quiz
            $quizVocab = array_slice($vocabIds, 0, $itemsPerSession);

            $quizId = $upsertActivity($lesson->id, 3, [
                'title'        => 'Mini Quiz',
                'description'  => 'Preguntas de opción múltiple',
                'item_type_id' => $mcqTypeId,
                'difficulty'   => 'easy',
                'max_score'    => count($quizVocab),
                'is_active'    => true,
                'config_json'  => json_encode([
                    'vocabulary_ids' => $quizVocab,
                    'options_count'  => 4,
                ]),
            ]);

            $wipeQuizQuestions($quizId);

            foreach ($quizVocab as $qIndex => $vocabId) {
                $questionId = DB::table('questions')->insertGetId([
                    'activity_id'   => $quizId,
                    'vocabulary_id' => $vocabId,
                    'prompt'        => 'Choose the correct answer',
                    'image_path'    => null,
                    'audio_path'    => null,
                    'order_index'   => $qIndex,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                $pool = $vocabIds;
                shuffle($pool);

                $desired = min(4, count($vocabIds));

                $options = array_values(array_unique(
                    array_merge([$vocabId], array_slice($pool, 0, max(0, $desired - 1)))
                ));

                $options = array_slice($options, 0, $desired);
                shuffle($options);

                foreach ($options as $oIndex => $optVocabId) {
                    DB::table('question_options')->insert([
                        'question_id' => $questionId,
                        'text'        => $wordsMap[$optVocabId] ?? null,
                        'image_path'  => null,
                        'is_correct'  => ((int)$optVocabId === (int)$vocabId),
                        'order_index' => $oIndex,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }
    }
}
