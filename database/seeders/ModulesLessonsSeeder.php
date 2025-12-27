<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesLessonsSeeder extends Seeder
{
    public function run(): void
    {
        $modulesByGrade = [
            1 => ['Animals', 'Colors'],
            2 => ['Fruits', 'Numbers'],
            3 => ['Home', 'Transport'],
        ];

        foreach ($modulesByGrade as $gradeId => $moduleTitles) {
            foreach ($moduleTitles as $mIndex => $title) {
                $moduleId = DB::table('modules')->updateOrInsert(
                    ['grade_id' => $gradeId, 'title' => $title],
                    [
                        'description' => "Módulo: {$title}",
                        'order_index' => $mIndex,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // obtener el id real
                $moduleRowId = DB::table('modules')->where('grade_id', $gradeId)->where('title', $title)->value('id');

                // 2 lecciones por módulo
                for ($l = 1; $l <= 2; $l++) {
                    DB::table('lessons')->updateOrInsert(
                        ['module_id' => $moduleRowId, 'title' => "{$title} - Lesson {$l}"],
                        [
                            'description' => "Lección {$l} del módulo {$title}",
                            'order_index' => $l - 1,
                            'estimated_time' => 10,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
