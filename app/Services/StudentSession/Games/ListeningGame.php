<?php

namespace App\Services\StudentSession\Games;

class ListeningGame
{
    public function buildItems($vocabCollection): array
    {
        $vocab = $vocabCollection->values();

        $items = [];
        foreach ($vocab as $target) {
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->map(fn($v) => [
                'id' => $v->id,
                'word_en' => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path' => $v->image_path,
                'audio_path' => $v->audio_path,
            ])->values()->all();

            $items[] = [
                'target' => [
                    'id' => $target->id,
                    'word_en' => $target->word_en,
                    'audio_path' => $target->audio_path,
                ],
                'options' => $options,
            ];
        }

        return $items;
    }

    public function hideDistractors(array $item): array
    {
        $targetId = (int)($item['target']['id'] ?? 0);

        $distractors = [];
        foreach (($item['options'] ?? []) as $opt) {
            $id = (int)($opt['id'] ?? 0);
            if ($id && $id !== $targetId) $distractors[] = $id;
        }

        shuffle($distractors);
        return array_slice($distractors, 0, min(2, count($distractors)));
    }
}
