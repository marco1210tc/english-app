<?php

namespace App\Services\ActivityEngine;

use App\Models\Activity;
use App\Models\Student;

class DragDropGrader
{
    /**
     * config_json ejemplo:
     * {
     *   "type": "drag_drop",
     *   "items": [
     *     {"item_key": "cat", "correct_target": "animals"},
     *     {"item_key": "blue", "correct_target": "colors"}
     *   ],
     *   "scoring": {
     *     "per_correct": 10,
     *     "per_wrong": -2,
     *     "min_score": 0,
     *     "max_score": 100
     *   }
     * }
     *
     * $payload['answers'] = [
     *   'cat'  => 'animals',
     *   'blue' => 'animals',
     * ]
     */
    public function grade(Activity $activity, Student $student, array $payload): GradedResult
    {
        $config = $activity->config_json;

        $items   = collect($config['items'] ?? []);
        $scoring = $config['scoring'] ?? [];

        $perCorrect = $scoring['per_correct'] ?? 10;
        $perWrong   = $scoring['per_wrong'] ?? 0;
        $minScore   = $scoring['min_score'] ?? 0;
        $maxScore   = $scoring['max_score'] ?? 100;

        $answers = $payload['answers'] ?? [];

        $rawScore = 0;
        $correctCount = 0;
        $wrongCount = 0;
        $perItemResults = [];

        foreach ($items as $item) {
            $itemKey       = $item['item_key'];
            $correctTarget = $item['correct_target'] ?? null;
            $userTarget    = $answers[$itemKey] ?? null;

            $isCorrect = $userTarget !== null && $userTarget === $correctTarget;

            if ($isCorrect) {
                $rawScore += $perCorrect;
                $correctCount++;
            } else {
                $rawScore += $perWrong;
                $wrongCount++;
            }

            $perItemResults[$itemKey] = [
                'correct'    => $isCorrect,
                'score'      => $isCorrect ? $perCorrect : $perWrong,
                'raw_answer' => $userTarget,
            ];
        }

        $score = max($minScore, min($rawScore, $maxScore));

        return new GradedResult(
            score: $score,
            correctCount: $correctCount,
            wrongCount: $wrongCount,
            perItemResults: $perItemResults,
            meta: [
                'raw_score' => $rawScore,
            ]
        );
    }
}
