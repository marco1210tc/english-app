<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\Activity;
use App\Models\Task;
use App\Models\Challenge;
use App\Models\Achievement;
use Illuminate\Support\Facades\DB;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear contenido genérico
        $this->command->info('Creando Temas, Actividades y Logros...');
        $topics = Topic::factory()->count(5)->create();
        $achievements = Achievement::factory()->count(10)->create();

        // Crear 3 tipos de actividades para cada tema
        $topics->each(function ($topic) {
            Activity::factory()->quiz()->create(['topic_id' => $topic->id]);
            Activity::factory()->memoryGame()->create(['topic_id' => $topic->id]);
            Activity::factory()->dragAndDrop()->create(['topic_id' => $topic->id]);
        });

        // Crear 10 Retos (Challenges) globales
        $activities = Activity::all();
        Challenge::factory()->count(10)->recycle($activities)->create();


        // 2. Crear Maestros y Estudiantes
        $this->command->info('Creando Maestros y Estudiantes...');
        $teachers = User::factory()->teacher()->count(5)->create();
        $students = User::factory()->student()->count(50)->create();

        // 3. Crear Aulas (Classrooms) y asignar todo
        $this->command->info('Creando Aulas, asignando Tareas y Entregas...');

        // Tomamos grupos de 10 estudiantes
        $studentGroups = $students->chunk(10);

        Classroom::factory()->count(5)
            ->recycle($teachers) // Reutiliza los 5 maestros
            ->create()
            ->each(function ($classroom, $index) use ($studentGroups, $activities, $achievements) {

                // 3a. Asignar estudiantes a esta aula
                if (!isset($studentGroups[$index])) return; // Evitar error si hay menos estudiantes

                $studentsInClass = $studentGroups[$index];
                $classroom->students()->attach($studentsInClass);

                // 3b. Crear Tareas (Tasks) para esta aula
                $tasks = Task::factory()->count(5)
                    ->recycle($activities) // Reutiliza actividades
                    ->create([
                        'classroom_id' => $classroom->id,
                        'teacher_id' => $classroom->teacher_id,
                    ]);

                // 3c. Crear Entregas (Submissions)
                $this->createSubmissions($tasks, $studentsInClass, $achievements);
            });
    }


    /**
     * Lógica para simular que los estudiantes completan tareas.
     */
    private function createSubmissions($tasks, $studentsInClass, $achievements)
    {
        foreach ($tasks as $task) {
            foreach ($studentsInClass as $student) {

                // Simular que solo el 80% de los estudiantes hizo la tarea
                if (rand(1, 100) > 80) {
                    continue;
                }

                $score = rand(50, 100);

                // Crear la entrega
                DB::table('task_submissions')->insert([
                    'task_id' => $task->id,
                    'user_id' => $student->id,
                    'score' => $score,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ¡IMPORTANTE! Actualizar el puntaje total del estudiante
                // (Esto lo haría un Observer en la app real, pero aquí lo simulamos)
                $student->increment('total_points', $score);

                // Simular que ganan un logro aleatorio (con una probabilidad del 10%)
                if (rand(1, 100) > 90) {
                    // 1. Obtener una lista de IDs de logros que el estudiante *YA TIENE*.
                    $unlockedIds = $student->achievements->pluck('id')->toArray();

                    // 2. Filtrar la colección global de logros ($achievements) para obtener solo
                    //    los que *NO HA* desbloqueado aún.
                    $availableAchievements = $achievements->filter(function ($achievement) use ($unlockedIds) {
                        return !in_array($achievement->id, $unlockedIds);
                    });

                    // 3. Si hay logros disponibles, adjuntar uno al azar.
                    if ($availableAchievements->isNotEmpty()) {
                        $student->achievements()->attach($availableAchievements->random());
                    }
                }
            }
        }
    }
}
