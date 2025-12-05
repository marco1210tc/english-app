<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentBadge;
use App\Models\StudentActivityAttempt as Attempt;

class BadgeService
{
    public function evaluateBadgesForActivityAttempt(Attempt $attempt): void
    {
        $student = $attempt->student;

        // Regla 1: primera actividad completada
        if ($student->activityAttempts()->count() === 1) {
            $this->giveBadgeIfNotHas($student, 'first_activity_completed', [
                'activity_id' => $attempt->activity_id,
            ]);
        }

        // Regla 2: score alto en una actividad (>= 90)
        if ($attempt->score >= 90) {
            $this->giveBadgeIfNotHas($student, 'high_scorer', [
                'activity_id' => $attempt->activity_id,
                'score'       => $attempt->score,
            ]);
        }

        // Regla 3: completar todas las actividades de la lección
        $lesson = $attempt->activity->lesson;
        if ($lesson && $this->lessonCompletedByStudent($lesson, $student)) {
            $this->giveBadgeIfNotHas($student, 'lesson_master_'.$lesson->id, [
                'lesson_id' => $lesson->id,
            ]);
        }
    }

    protected function giveBadgeIfNotHas(Student $student, string $code, array $meta = []): void
    {
        $badge = Badge::where('code', $code)->first();

        if (! $badge) {
            return;
        }

        $already = StudentBadge::where('student_id', $student->id)
            ->where('badge_id', $badge->id)
            ->exists();

        if ($already) {
            return;
        }

        StudentBadge::create([
            'student_id' => $student->id,
            'badge_id'   => $badge->id,
            'awarded_at' => now(),
            'meta_json'  => $meta,
        ]);
    }

    protected function lessonCompletedByStudent(Lesson $lesson, Student $student): bool
    {
        $activityIds = $lesson->activities()->pluck('id');

        if ($activityIds->isEmpty()) {
            return false;
        }

        $completedCount = Attempt::where('student_id', $student->id)
            ->whereIn('activity_id', $activityIds)
            ->where('score', '>=', 60) // umbral de aprobado
            ->distinct('activity_id')
            ->count('activity_id');

        return $completedCount === $activityIds->count();
    }
}
