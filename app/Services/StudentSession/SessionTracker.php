<?php

namespace App\Services\StudentSession;

use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;

class SessionTracker
{
    /**
     * Reusa el último attempt in_progress si existe; si no crea uno.
     */
    public function startOrResumeAttempt(int $studentId, int $baseActivityId): StudentActivityAttempt
    {
        $attempt = StudentActivityAttempt::query()
            ->where('student_id', $studentId)
            ->where('activity_id', $baseActivityId)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        if ($attempt) return $attempt;

        return StudentActivityAttempt::create([
            'student_id'      => $studentId,
            'activity_id'     => $baseActivityId,
            'score_obtained'  => 0,
            'max_score'       => 0,
            'started_at'      => now(),
            'status'          => 'in_progress',
            'attempt_number'  => 1,
        ]);
    }

    public function storeItemAttempt(
        int $activityAttemptId,
        string $itemKey,
        bool $isCorrect,
        int $attempts,
        int $hintsUsed,
        array $response,
        ?int $itemStartedAtTs
    ): void {
        $seconds = 0;
        if ($itemStartedAtTs) {
            $seconds = max(0, now()->timestamp - $itemStartedAtTs);
        }

        StudentItemAttempt::create([
            'activity_attempt_id' => $activityAttemptId,
            'item_key'            => $itemKey,
            'is_correct'          => $isCorrect,
            'attempts'            => $attempts,
            'time_spent_seconds'  => $seconds,
            'hints_used'          => $hintsUsed,
            'response_json'       => $response,
        ]);
    }

    public function finishCompleted(int $activityAttemptId): void
    {
        $items = StudentItemAttempt::query()
            ->where('activity_attempt_id', $activityAttemptId)
            ->get(['is_correct']);

        $max   = $items->count();
        $score = $items->where('is_correct', true)->count();

        StudentActivityAttempt::whereKey($activityAttemptId)->update([
            'max_score'      => $max,
            'score_obtained' => $score,
            'completed_at'   => now(),
            'status'         => 'completed',
        ]);
    }

    public function finishAbandoned(int $activityAttemptId): void
    {
        StudentActivityAttempt::whereKey($activityAttemptId)->update([
            'completed_at' => now(),
            'status'       => 'abandoned',
        ]);
    }
}
