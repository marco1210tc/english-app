<?php

namespace App\Services\StudentSession;

use App\Models\ClassroomLessonAssignment;
use Illuminate\Support\Collection;

class SessionContentBuilder
{

    public function resolveActivitiesByType($lesson): array
    {
        $acts = $lesson->activities ?? collect();

        // en tu BD el key confirmado es 'listening'
        $listening = $acts->first(fn($a) => optional($a->itemType)->key === 'listening')
            ?? $acts->first();

        $matching  = $acts->first(fn($a) => optional($a->itemType)->key === 'matching');
        $quiz      = $acts->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        return compact('listening', 'matching', 'quiz');
    }

    public function loadAssignmentForStudent(int $assignmentId, int $classroomId): ClassroomLessonAssignment
    {
        return ClassroomLessonAssignment::query()
            ->with([
                'lesson.vocabulary' => fn($q) => $q->where('status', 'published')
                    ->orderBy('lesson_vocabulary.order_index'),
                'lesson.activities.itemType',
                'lesson.activities.questions.options', // quiz
            ])
            ->where('classroom_id', $classroomId)
            ->where('status', 'active')
            ->findOrFail($assignmentId);
    }

    public function buildFlashcards(Collection $vocab, int $take = 5): array
    {
        $cards = $vocab->take($take)->map(fn($v) => [
            'id'             => $v->id,
            'word_en'        => $v->word_en,
            'translation_es' => $v->translation_es,
            'image_path'     => $v->image_path,
            'audio_path'     => $v->audio_path,
        ])->values()->all();

        if (count($cards) === 0) {
            return [[
                'id' => 0,
                'word_en' => 'No items',
                'translation_es' => 'Sin vocabulario publicado',
                'image_path' => null,
                'audio_path' => null,
            ]];
        }

        return $cards;
    }

    public function buildListeningItems(Collection $vocab, int $take = 8): array
    {
        $vocab = $vocab->take($take)->values();
        $items = [];

        foreach ($vocab as $target) {
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->map(fn($v) => [
                'id'             => $v->id,
                'word_en'        => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path'     => $v->image_path,
                'audio_path'     => $v->audio_path,
            ])->values()->all();

            $items[] = [
                'target' => [
                    'id'         => $target->id,
                    'word_en'    => $target->word_en,
                    'audio_path' => $target->audio_path,
                ],
                'options' => $options,
            ];
        }

        return $items;
    }

    /**
     * Matching “duplicado visual”: 2 cartas idénticas por vocab (misma pair_key).
     * Devuelve: [pairs, cards]
     */
    public function buildMatching(Collection $vocab, int $take = 6): array
    {
        $vocab = $vocab->take($take)->values();

        $pairs = [];
        $cards = [];
        $cardId = 1;

        foreach ($vocab as $v) {
            $pairKey = 'v' . $v->id;

            $pairs[] = [
                'pair_key'       => $pairKey,
                'vocab_id'       => $v->id,
                'word_en'        => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path'     => $v->image_path,
            ];

            // carta A
            $cards[] = [
                'card_id'   => $cardId++,
                'pair_key'  => $pairKey,
                'vocab_id'  => $v->id,
                'image_path' => $v->image_path,
                'label'     => $v->word_en,
            ];
            // carta B (duplicada)
            $cards[] = [
                'card_id'   => $cardId++,
                'pair_key'  => $pairKey,
                'vocab_id'  => $v->id,
                'image_path' => $v->image_path,
                'label'     => $v->word_en,
            ];
        }

        shuffle($cards);

        return [$pairs, $cards];
    }

    /**
     * Quiz desde BD: Activity(multiple_choice) -> questions -> options
     * Fallback si no hay preguntas: genera preguntas desde vocab (sin migraciones).
     */
    public function buildQuizQuestions($quizActivity, Collection $vocab, int $take = 5): array
    {
        // 1) BD (Activity -> Questions -> Options)
        if (
            $quizActivity
            && $quizActivity->relationLoaded('questions')
            && $quizActivity->questions
            && $quizActivity->questions->count() > 0
        ) {
            $questions = $quizActivity->questions
                ->sortBy('order_index')
                ->values()
                ->take($take);

            $out = [];

            foreach ($questions as $q) {
                $opts = ($q->options ?? collect())
                    ->sortBy('order_index')
                    ->values();

                // Reglas mínimas para que no reviente el juego:
                if ($opts->count() < 2) {
                    continue; // pregunta inválida
                }

                $correct = $opts->firstWhere('is_correct', true);

                // Si no hay correcta, la pregunta es inválida (seed incompleto)
                if (!$correct) {
                    continue;
                }

                $out[] = [
                    'id'                => (int) $q->id,
                    'prompt'            => $q->prompt ?: 'Elige la respuesta correcta',
                    'correct_option_id' => (int) $correct->id,
                    'options'           => $opts->map(fn($o) => [
                        'id'         => (int) $o->id,
                        'text'       => $o->text,        // nullable ok
                        'image_path' => $o->image_path,  // por si luego usas opciones con imagen
                    ])->all(),
                ];
            }

            // Si después de filtrar quedó vacío, caemos al fallback
            if (!empty($out)) {
                return $out;
            }
        }

        // 2) FALLBACK (si el quiz no está sembrado completo todavía)
        $vocab = $vocab->values();
        $out = [];

        foreach ($vocab->take($take) as $target) {
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->values()->map(fn($v) => [
                'id'         => (int) $v->id,
                'text'       => $v->word_en,
                'image_path' => $v->image_path,
            ])->all();

            $out[] = [
                'id'                => (int) $target->id,
                'prompt'            => "¿Cuál es: {$target->translation_es}?",
                'correct_option_id' => (int) $target->id,
                'options'           => $options,
            ];
        }

        return $out;
    }
}
