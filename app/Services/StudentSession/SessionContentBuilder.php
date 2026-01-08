<?php

namespace App\Services\StudentSession;

use App\Models\ClassroomLessonAssignment;

class SessionContentBuilder
{
    public function loadAssignmentForStudent(int $assignmentId, int $studentClassroomId)
    {
        return ClassroomLessonAssignment::query()
            ->with([
                'lesson.vocabulary' => fn($q) => $q->where('status', 'published')
                    ->orderBy('lesson_vocabulary.order_index'),
                'lesson.activities.itemType',
                'lesson.activities.questions.options',
            ])
            ->where('classroom_id', $studentClassroomId)
            ->where('status', 'active')
            ->findOrFail($assignmentId);
    }

    public function resolveActivityIds($lesson): array
    {
        $activities = $lesson->activities ?? collect();

        $listening = $activities->first(fn($a) => optional($a->itemType)->key === 'listening') ?? $activities->first();
        $matching  = $activities->first(fn($a) => optional($a->itemType)->key === 'matching');
        $quiz      = $activities->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        return [
            'listening' => $listening?->id,
            'matching'  => $matching?->id,
            'quiz'      => $quiz?->id,
        ];
    }

    public function buildFlashcards($vocab, int $take = 5): array
    {
        $cards = $vocab->take($take)->map(fn($v) => [
            'id' => $v->id,
            'word_en' => $v->word_en,
            'translation_es' => $v->translation_es,
            'image_path' => $v->image_path,
            'audio_path' => $v->audio_path,
        ])->values()->all();

        if (count($cards) > 0) return $cards;

        return [[
            'id' => 0,
            'word_en' => 'No items',
            'translation_es' => 'Sin vocabulario publicado',
            'image_path' => null,
            'audio_path' => null,
        ]];
    }
}
