<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonVocabularySeeder extends Seeder
{
    public function run(): void
    {
        $vocab = DB::table('vocabulary')->orderBy('id')->pluck('id')->all();
        $lessons = DB::table('lessons')->orderBy('id')->pluck('id')->all();

        // Reparto simple: 4 vocab por lección
        $chunkSize = 4;
        $cursor = 0;

        foreach ($lessons as $lessonId) {
            $slice = array_slice($vocab, $cursor, $chunkSize);
            if (count($slice) < $chunkSize) {
                $cursor = 0;
                $slice = array_slice($vocab, $cursor, $chunkSize);
            }

            foreach ($slice as $idx => $vocabId) {
                DB::table('lesson_vocabulary')->updateOrInsert(
                    ['lesson_id' => $lessonId, 'vocabulary_id' => $vocabId],
                    ['order_index' => $idx]
                );
            }

            $cursor += $chunkSize;
        }
    }
}
