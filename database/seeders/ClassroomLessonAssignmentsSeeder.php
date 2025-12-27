<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomLessonAssignmentsSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = DB::table('classrooms')->get(['id', 'grade_id', 'teacher_id']);

        foreach ($classrooms as $classroom) {
            $lessonIds = DB::table('lessons')
                ->join('modules', 'modules.id', '=', 'lessons.module_id')
                ->where('modules.grade_id', $classroom->grade_id)
                ->pluck('lessons.id');

            foreach ($lessonIds as $lessonId) {
                DB::table('classroom_lesson_assignments')->updateOrInsert(
                    ['classroom_id' => $classroom->id, 'lesson_id' => $lessonId],
                    [
                        'assigned_by' => $classroom->teacher_id ?? DB::table('users')->where('role','admin')->value('id'),
                        'assigned_at' => now(),
                        'due_at' => null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
