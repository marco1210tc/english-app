<?php

namespace App\Services\ActivityEngine;

use App\Models\Activity;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentQuestionAttempt;
use App\Events\StudentActivityCompleted;

class FinishActivityAttemptService
{
    public function __construct(
        protected GradeActivityService $gradeActivityService,
    ) {}

    /**
     * $payload es lo que envía el frontend (respuestas, etc.)
     */
    public function finish(
        Activity $activity,
        Student $student,
        array $payload,
        ?StudentActivityAttempt $attempt = null
    ): StudentActivityAttempt {
        // 1. Aseguramos que exista el attempt
        if (! $attempt) {
            $attempt = StudentActivityAttempt::create([
                'student_id'  => $student->id,
                'activity_id' => $activity->id,
                'started_at'  => now(), // o payload['started_at'] si lo envías
            ]);
        }

        // 2. Calcular duración
        $startedAt  = $attempt->started_at ?? now();
        $finishedAt = now();
        $duration   = $finishedAt->diffInSeconds($startedAt);

        // 3. Grading
        $graded = $this->gradeActivityService->grade($activity, $student, $payload);

        // 4. Actualizar attempt
        $attempt->update([
            'finished_at'      => $finishedAt,
            'duration_seconds' => $duration,
            'score'            => $graded->score,
            'max_score'        => 100, // por ahora fijo; si quieres, saca de config
            'correct_count'    => $graded->correctCount,
            'wrong_count'      => $graded->wrongCount,
            'raw_payload'      => $payload,
            'meta_json'        => $graded->meta,
        ]);

        // 5. Guardar detalles por pregunta/item
        $this->storePerItemAttempts($attempt, $graded);

        // 6. Disparar evento de dominio
        event(new StudentActivityCompleted($attempt));

        return $attempt;
    }

    protected function storePerItemAttempts(
        StudentActivityAttempt $attempt,
        GradedResult $graded
    ): void {
        foreach ($graded->perItemResults as $key => $result) {
            StudentQuestionAttempt::create([
                'student_activity_attempt_id' => $attempt->id,
                'question_id'  => is_numeric($key) ? (int) $key : null,
                'item_key'     => ! is_numeric($key) ? (string) $key : null,
                'is_correct'   => (bool) ($result['correct'] ?? false),
                'score'        => $result['score'] ?? null,
                'raw_answer_json' => $result['raw_answer'] ?? null,
            ]);
        }
    }
}
