<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestItemsSeeder extends Seeder
{
    public function run(): void
    {
        $typeVocabulary = DB::table('item_types')->where('key', 'vocabulary')->value('id');
        $typeMCQ        = DB::table('item_types')->where('key', 'multiple_choice')->value('id');
        $typeListening  = DB::table('item_types')->where('key', 'listening')->value('id');

        if (!$typeVocabulary || !$typeMCQ || !$typeListening) {
            throw new \RuntimeException("Faltan item_types (vocabulary/multiple_choice/listening). Revisa ItemTypesSeeder.");
        }

        foreach ([1,2,3] as $gradeId) {
            $preId  = DB::table('tests')->where('grade_id', $gradeId)->where('type', 'pre')->value('id');
            $postId = DB::table('tests')->where('grade_id', $gradeId)->where('type', 'post')->value('id');

            if (!$preId || !$postId) {
                throw new \RuntimeException("Faltan tests pre/post para grade_id={$gradeId}. Ejecuta TestsSeeder.");
            }

            // Pool vocab por grado
            $vocabIds = DB::table('lesson_vocabulary')
                ->join('lessons', 'lessons.id', '=', 'lesson_vocabulary.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->distinct()
                ->pluck('lesson_vocabulary.vocabulary_id')
                ->take(8)
                ->all();

            // Preguntas de actividades MCQ del grado
            $questionIds = DB::table('questions')
                ->join('activities', 'activities.id', '=', 'questions.activity_id')
                ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->where('activities.item_type_id', $typeMCQ)
                ->orderBy('questions.id')
                ->take(5)
                ->pluck('questions.id')
                ->all();

            // 1 actividad de tipo listening del grado
            $activityId = DB::table('activities')
                ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->where('activities.item_type_id', $typeListening)
                ->value('activities.id');

            $seedTest = function (int $testId) use ($typeVocabulary, $typeMCQ, $typeListening, $vocabIds, $questionIds, $activityId) {
                $order = 0;

                foreach ($vocabIds as $vid) {
                    DB::table('test_items')->updateOrInsert(
                        ['test_id' => $testId, 'item_type_id' => $typeVocabulary, 'ref_id' => $vid],
                        ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                    );
                }

                foreach ($questionIds as $qid) {
                    DB::table('test_items')->updateOrInsert(
                        ['test_id' => $testId, 'item_type_id' => $typeMCQ, 'ref_id' => $qid],
                        ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                    );
                }

                if ($activityId) {
                    DB::table('test_items')->updateOrInsert(
                        ['test_id' => $testId, 'item_type_id' => $typeListening, 'ref_id' => $activityId],
                        ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            };

            $seedTest($preId);
            $seedTest($postId);
        }
    }
}
