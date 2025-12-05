<?php

namespace App\Services\ActivityEngine;

use App\Models\Activity;
use App\Models\Student;

class QuizGrader
{
    /**
     * $payload['answers'] = [
     *     question_id => [option_ids...],
     * ]
     */
    public function grade(Activity $activity, Student $student, array $payload): GradedResult
    {
        $answers = $payload['answers'] ?? [];

        $config = $activity->config_json;
        $questionConfigs = collect($config['questions'] ?? []);

        $totalWeight = $questionConfigs->sum('weight') ?: 1;
        $earnedWeight = 0;
        $perItemResults = [];
        $correctCount = 0;
        $wrongCount = 0;

        foreach ($questionConfigs as $qConfig) {
            $questionId     = $qConfig['question_id'];
            $weight         = $qConfig['weight'] ?? 1;
            $correctOptions = collect($qConfig['correct_option_ids'] ?? [])->sort()->values()->all();
            $userOptions    = collect($answers[$questionId] ?? [])->sort()->values()->all();

            $isCorrect = $userOptions === $correctOptions;

            if ($isCorrect) {
                $earnedWeight += $weight;
                $correctCount++;
            } else {
                $wrongCount++;
            }

            $perItemResults[$questionId] = [
                'correct'     => $isCorrect,
                'weight'      => $weight,
                'score'       => $isCorrect ? $weight : 0,
                'raw_answer'  => $userOptions,
            ];
        }

        $score = round(($earnedWeight / $totalWeight) * 100, 2);

        return new GradedResult(
            score: $score,
            correctCount: $correctCount,
            wrongCount: $wrongCount,
            perItemResults: $perItemResults,
            meta: []
        );
    }
}
