<?php

namespace App\Services\Progress;

use App\Models\ProgressSnapshot;
use App\Models\StudentActivityAttempt;

class UpdateStudentProgressService
{
    public function fromActivityAttempt(StudentActivityAttempt $attempt): void
    {
        $student   = $attempt->student;
        $activity  = $attempt->activity;
        $lesson    = $activity->lesson;
        $module    = $lesson?->module;
        $classroom = $student->classrooms()->latest('pivot_created_at')->first(); // heurística simple

        // Ejemplo de cálculo simple para MVP:
        $totalActivities = $lesson
            ? $lesson->activities()->count()
            : 0;

        $completedActivities = $lesson
            ? $student->activityAttempts()
                ->whereIn('activity_id', $lesson->activities()->pluck('id'))
                ->where('score', '>=', 60)
                ->distinct('activity_id')
                ->count('activity_id')
            : 0;

        $avgScore = $student->activityAttempts()
            ->where('activity_id', $activity->id)
            ->avg('score') ?? $attempt->score;

        $data = [
            'completed_activities' => $completedActivities,
            'total_activities'     => $totalActivities,
            'completion_ratio'     => $totalActivities > 0 ? $completedActivities / $totalActivities : null,
            'last_activity_id'     => $activity->id,
            'last_score'           => $attempt->score,
            'avg_score_for_activity' => $avgScore,
        ];

        ProgressSnapshot::create([
            'student_id'   => $student->id,
            'classroom_id' => $classroom?->id,
            'module_id'    => $module?->id,
            'lesson_id'    => $lesson?->id,
            'taken_at'     => now(),
            'data_json'    => $data,
        ]);
    }
}
