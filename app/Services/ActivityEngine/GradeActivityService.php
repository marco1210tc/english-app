<?php

namespace App\Services\ActivityEngine;

use App\Models\Activity;
use App\Models\Student;
use App\Enums\ActivityType;

class GradeActivityService
{
    public function __construct(
        protected QuizGrader $quizGrader,
        protected DragDropGrader $dragDropGrader,
    ) {}

    public function grade(Activity $activity, Student $student, array $payload): GradedResult
    {
        $type = $activity->config_json['type'] ?? ActivityType::QUIZ->value;
        // var_dump($type); // Verifica el valor de $type
        $activityType = ActivityType::from($type);

        return match ($activityType) {
            ActivityType::QUIZ      => $this->quizGrader->grade($activity, $student, $payload),
            ActivityType::DRAG_DROP => $this->dragDropGrader->grade($activity, $student, $payload),
            default                 => throw new \RuntimeException("Unsupported activity type: {$activityType->value}")
        };
    }
}
