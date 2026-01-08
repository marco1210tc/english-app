<?php

namespace App\Services\StudentSession\Games;

class MultipleChoiceGame
{
    public function buildFromActivity($quizActivity): array
    {
        if (!$quizActivity) return [];

        $questions = $quizActivity->questions ?? collect();
        $questions = $questions->sortBy('order_index')->take(5);

        $out = [];
        foreach ($questions as $q) {
            $opts = ($q->options ?? collect())->sortBy('order_index')->values();
            $correct = $opts->firstWhere('is_correct', true);

            $out[] = [
                'id' => $q->id,
                'prompt' => $q->prompt ?? 'Elige la respuesta correcta',
                'correct_option_id' => $correct?->id,
                'options' => $opts->map(fn($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                ])->all(),
            ];
        }

        return $out;
    }
}
