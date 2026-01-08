<?php

namespace App\Services\StudentSession\Games;

class MatchingGame
{
    public function buildFromVocab($vocabCollection): array
    {
        $vocab = $vocabCollection->values();

        $pairs = [];
        $cards = [];
        $cardId = 1;

        foreach ($vocab as $v) {
            $pairKey = 'v' . $v->id;

            $pairs[] = [
                'pair_key' => $pairKey,
                'vocab_id' => $v->id,
                'word_en' => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path' => $v->image_path,
            ];

            $cards[] = [
                'card_id' => $cardId++,
                'pair_key' => $pairKey,
                'vocab_id' => $v->id,
                'image_path' => $v->image_path,
                'label' => $v->word_en,
            ];
            $cards[] = [
                'card_id' => $cardId++,
                'pair_key' => $pairKey,
                'vocab_id' => $v->id,
                'image_path' => $v->image_path,
                'label' => $v->word_en,
            ];
        }

        shuffle($cards);

        return [
            'pairs' => $pairs,
            'cards' => $cards,
        ];
    }

    public function pickCard(array $cards, int $firstId, int $secondId): bool
    {
        $first = $this->findCard($cards, $firstId);
        $second = $this->findCard($cards, $secondId);

        if (!$first || !$second) return false;

        return ($first['pair_key'] ?? null) && (($first['pair_key'] ?? null) === ($second['pair_key'] ?? null));
    }

    public function hideDistractorCards(array $cards, array $solved, ?int $first, ?int $second): array
    {
        $available = array_values(array_filter($cards, function($c) use ($solved, $first, $second) {
            $id = (int)($c['card_id'] ?? 0);
            if (!$id) return false;
            if (in_array($id, $solved, true)) return false;
            if ($first && $id === (int)$first) return false;
            if ($second && $id === (int)$second) return false;
            return true;
        }));

        $ids = array_map(fn($c) => (int)$c['card_id'], $available);
        shuffle($ids);
        return array_slice($ids, 0, min(2, count($ids)));
    }

    public function findCard(array $cards, int $cardId): ?array
    {
        foreach ($cards as $c) {
            if ((int)($c['card_id'] ?? 0) === (int)$cardId) return $c;
        }
        return null;
    }
}
