<?php

namespace App\Services\ActivityEngine;

class GradedResult
{
    public function __construct(
        public readonly float $score,
        public readonly int $correctCount,
        public readonly int $wrongCount,
        public readonly array $perItemResults = [], // [key => ['correct' => bool, 'score' => float|null, ...]]
        public readonly ?array $meta = []          // tiempos, etc.
    ) {}
}