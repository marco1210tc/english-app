<?php

namespace App\Livewire\Student\Session;

use Livewire\Component;
use App\Models\ClassroomLessonAssignment;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;

class Player extends Component
{
    public int $assignmentId;

    public string $state = 'intro';

    public int $flashIndex = 0;
    public int $listenIndex = 0;

    public array $flashcards = [];
    public array $listenItems = [];

    public int $listenAttemptNo = 1;
    public int $listenHintsUsed = 0;
    public array $listenHidden = []; // ids ocultos
    public bool $listenLocked = false;
    public ?string $lastFeedback = null;

    // ---- Tracking ----
    public ?int $activityId = null;
    public ?int $activityAttemptId = null;
    public ?int $itemStartedAtTs = null;

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $assignment = ClassroomLessonAssignment::query()
            ->with([
                'lesson.vocabulary' => fn($q) => $q->where('status', 'published')
                    ->orderBy('lesson_vocabulary.order_index'),
                'lesson.activities.itemType',
            ])
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active')
            ->findOrFail($assignmentId);

        $vocab = $assignment->lesson->vocabulary;

        $this->flashcards = $vocab->take(5)->map(fn($v) => [
            'id' => $v->id,
            'word_en' => $v->word_en,
            'translation_es' => $v->translation_es,
            'image_path' => $v->image_path,
            'audio_path' => $v->audio_path,
        ])->values()->all();

        if (count($this->flashcards) === 0) {
            $this->flashcards = [[
                'id' => 0,
                'word_en' => 'No items',
                'translation_es' => 'Sin vocabulario publicado',
                'image_path' => null,
                'audio_path' => null,
            ]];
        }

        $this->buildListenItemsFrom($vocab->take(8)); // simple

        // activity tracking: itemType.key = 'listening'
        $activity = $assignment->lesson->activities
            ->first(fn($a) => optional($a->itemType)->key === 'listening');

        if (!$activity) {
            $activity = $assignment->lesson->activities->first();
        }

        $this->activityId = $activity?->id;
    }

    public function start(): void
    {
        $student = auth('student')->user();

        $this->state = 'flashcards';
        $this->flashIndex = 0;

        if (!$this->activityId) {
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $attempt = StudentActivityAttempt::create([
            'student_id' => $student->id,
            'activity_id' => $this->activityId,
            'score_obtained' => 0,
            'max_score' => 0,
            'started_at' => now(),
            'status' => 'in_progress',
            'attempt_number' => 1,
        ]);

        $this->activityAttemptId = $attempt->id;
        $this->itemStartedAtTs = now()->timestamp;
    }

    public function nextFlashcard(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->storeItemAttempt(
            itemKey: 'flash_' . $this->flashIndex,
            isCorrect: true,
            attempts: 1,
            hintsUsed: 0,
            response: ['type' => 'flashcard', 'vocab_id' => $this->flashcards[$this->flashIndex]['id'] ?? null]
        );

        $this->flashIndex++;

        if ($this->flashIndex >= count($this->flashcards)) {
            $this->state = 'listening';
            $this->listenIndex = 0;
            $this->resetListenItemState();
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $this->itemStartedAtTs = now()->timestamp;
    }

    public function pickListenOption(int $optIndex): void
    {
        if ($this->state !== 'listening') return;
        if ($this->listenLocked) return;

        $this->listenLocked = true;

        $item = $this->listenItems[$this->listenIndex] ?? null;
        if (!$item) {
            $this->finishAttemptAsCompleted();
            $this->state = 'summary';
            return;
        }

        $options = $item['options'];
        $picked = $options[$optIndex] ?? null;

        $isCorrect = $picked && ((int)$picked['id'] === (int)$item['target']['id']);

        $this->storeItemAttempt(
            itemKey: 'listening_' . $this->listenIndex,
            isCorrect: $isCorrect,
            attempts: $this->listenAttemptNo,
            hintsUsed: $this->listenHintsUsed,
            response: [
                'type' => 'listening',
                'target_vocab_id' => $item['target']['id'],
                'picked_vocab_id' => $picked['id'] ?? null,
                'opt_index' => $optIndex,
                'attempt_no' => $this->listenAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->lastFeedback = 'correct';
            $this->advanceListenItem();
            return;
        }

        $this->lastFeedback = 'wrong';

        // hints (simple)
        if ($this->listenAttemptNo === 1) {
            $this->listenHintsUsed += 1;
            $this->dispatch('listen:play-audio');
        } elseif ($this->listenAttemptNo === 2) {
            $this->listenHintsUsed += 1;
            $this->applyHideDistractors();
        } else {
            $this->listenHintsUsed += 1;
            $this->dispatch('listen:reveal-correct');
        }

        $this->listenAttemptNo = min(3, $this->listenAttemptNo + 1);
        $this->listenLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- helpers ----------------

    private function buildListenItemsFrom($vocabCollection): void
    {
        $vocab = $vocabCollection->values();

        $items = [];
        foreach ($vocab as $target) {
            // opciones: target + 3 distractores
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->map(fn($v) => [
                'id' => $v->id,
                'word_en' => $v->word_en,
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

        $this->listenItems = $items;
    }

    private function resetListenItemState(): void
    {
        $this->listenAttemptNo = 1;
        $this->listenHintsUsed = 0;
        $this->listenHidden = [];
        $this->listenLocked = false;
        $this->lastFeedback = null;
    }

    private function applyHideDistractors(): void
    {
        $item = $this->listenItems[$this->listenIndex] ?? null;
        if (!$item) return;

        $targetId = (int)($item['target']['id'] ?? 0);

        $distractors = [];
        foreach (($item['options'] ?? []) as $opt) {
            $id = (int)($opt['id'] ?? 0);
            if ($id && $id !== $targetId) $distractors[] = $id;
        }

        shuffle($distractors);
        $this->listenHidden = array_slice($distractors, 0, min(2, count($distractors)));
    }


    private function advanceListenItem(): void
    {
        $this->listenIndex++;

        if ($this->listenIndex >= count($this->listenItems)) {
            $this->finishAttemptAsCompleted();
            $this->state = 'summary';
            return;
        }

        $this->resetListenItemState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function storeItemAttempt(
        string $itemKey,
        bool $isCorrect,
        int $attempts,
        int $hintsUsed,
        array $response
    ): void {
        if (!$this->activityAttemptId) return;

        $seconds = 0;
        if ($this->itemStartedAtTs) {
            $seconds = max(0, now()->timestamp - $this->itemStartedAtTs);
        }

        StudentItemAttempt::create([
            'activity_attempt_id' => $this->activityAttemptId,
            'item_key' => $itemKey,
            'is_correct' => $isCorrect,
            'attempts' => $attempts,
            'time_spent_seconds' => $seconds,
            'hints_used' => $hintsUsed,
            'response_json' => $response,
        ]);
    }

    private function finishAttemptAsCompleted(): void
    {
        if (!$this->activityAttemptId) return;

        StudentActivityAttempt::whereKey($this->activityAttemptId)->update([
            'completed_at' => now(),
            'status' => 'completed',
        ]);
    }

    public function render()
    {
        return view('livewire.student.session.player');
    }
}
