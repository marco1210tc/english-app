<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestItemsSeeder extends Seeder
{
    public function run(): void
    {
        $typeVocabulary = DB::table('item_types')->where('key', 'vocabulary')->value('id');
        $typeQuizQ      = DB::table('item_types')->where('key', 'quiz_question')->value('id');
        $typeActivity   = DB::table('item_types')->where('key', 'listen_choose')->value('id'); // para referencia, usaremos actividades

        foreach ([1,2,3] as $gradeId) {
            $preId  = DB::table('tests')->where('grade_id', $gradeId)->where('type', 'pre')->value('id');
            $postId = DB::table('tests')->where('grade_id', $gradeId)->where('type', 'post')->value('id');

            // Pool vocab por grado: vocab asociado a lecciones del grado
            $vocabIds = DB::table('lesson_vocabulary')
                ->join('lessons', 'lessons.id', '=', 'lesson_vocabulary.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->distinct()
                ->pluck('lesson_vocabulary.vocabulary_id')
                ->take(8) // ejemplo
                ->all();

            // Quiz questions del grado (de actividades quiz)
            $questionIds = DB::table('questions')
                ->join('activities', 'activities.id', '=', 'questions.activity_id')
                ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->orderBy('questions.id')
                ->take(5)
                ->pluck('questions.id')
                ->all();

            // 1 actividad de tipo listen_choose del grado para modo evaluación
            $activityId = DB::table('activities')
                ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $gradeId)
                ->where('activities.activity_type', 'listen_choose')
                ->value('activities.id');

            $order = 0;

            // PRE: vocab + quiz questions + 1 activity
            foreach ($vocabIds as $vid) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $preId, 'item_type_id' => $typeVocabulary, 'ref_id' => $vid],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            foreach ($questionIds as $qid) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $preId, 'item_type_id' => $typeQuizQ, 'ref_id' => $qid],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            if ($activityId) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $preId, 'item_type_id' => DB::table('item_types')->where('key','listen_choose')->value('id'), 'ref_id' => $activityId],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // POST: mismo set (o podrías variar)
            $order = 0;
            foreach ($vocabIds as $vid) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $postId, 'item_type_id' => $typeVocabulary, 'ref_id' => $vid],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            foreach ($questionIds as $qid) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $postId, 'item_type_id' => $typeQuizQ, 'ref_id' => $qid],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            if ($activityId) {
                DB::table('test_items')->updateOrInsert(
                    ['test_id' => $postId, 'item_type_id' => DB::table('item_types')->where('key','listen_choose')->value('id'), 'ref_id' => $activityId],
                    ['order_index' => $order++, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
