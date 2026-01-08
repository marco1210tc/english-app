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
    public array $listenHidden = []; // ids (vocab id) ocultos
    public bool $listenLocked = false;
    public ?string $lastFeedback = null;

    // ---- MATCHING ----
    public array $matchPairs = [];        // pares base (target)
    public array $matchCards = [];        // cartas (duplicadas y mezcladas)
    public array $matchSolved = [];       // card_ids resueltos
    public array $matchHiddenCards = [];  // card_ids ocultos (pista)
    public ?int $matchFirst = null;       // card_id
    public ?int $matchSecond = null;      // card_id
    public int $matchAttemptNo = 1;
    public int $matchHintsUsed = 0;
    public bool $matchLocked = false;
    public ?string $matchFeedback = null; // correct | wrong | null

    // ---- MULTIPLE CHOICE ----
    public array $quizQuestions = [];  // [{id,prompt,options:[{id,text}],correct_option_id}]
    public int $quizIndex = 0;
    public int $quizAttemptNo = 1;
    public int $quizHintsUsed = 0;
    public bool $quizLocked = false;
    public ?string $quizFeedback = null;

    // ---- Tracking ----
    public ?int $activityIdListening = null;
    public ?int $activityIdMatching  = null;
    public ?int $activityIdQuiz      = null;

    // Una “sesión” por assignment (reusamos in_progress si existe)
    public ?int $activityAttemptId = null;
    public ?int $itemStartedAtTs = null;

    // cache
    private $assignment;

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $this->assignment = ClassroomLessonAssignment::query()
            ->with([
                'lesson.vocabulary' => fn($q) => $q->where('status', 'published')
                    ->orderBy('lesson_vocabulary.order_index'),
                'lesson.activities.itemType',
                // para quiz (si usas questions/options)
                'lesson.activities.questions.options',
            ])
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active')
            ->findOrFail($assignmentId);

        $vocab = $this->assignment->lesson->vocabulary;

        // Flashcards (5)
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

        // Listening items (hasta 8)
        $listenVocab = $vocab->take(8);
        $this->buildListenItemsFrom($listenVocab);

        // Matching: tomamos 6 (o menos) para duplicado visual (opción 1 más adelante)
        $matchVocab = $vocab->take(6);
        $this->buildMatchingFrom($matchVocab);

        // Resolver activities por itemType.key
        $activities = $this->assignment->lesson->activities;

        $listening = $activities->first(fn($a) => optional($a->itemType)->key === 'listening')
            ?? $activities->first();

        $matching  = $activities->first(fn($a) => optional($a->itemType)->key === 'matching');
        $quiz      = $activities->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        $this->activityIdListening = $listening?->id;
        $this->activityIdMatching  = $matching?->id;
        $this->activityIdQuiz      = $quiz?->id;

        // Construir preguntas de quiz desde actividad multiple_choice (si existe)
        $this->buildQuizFromActivity($quiz);
    }

    public function start(): void
    {
        $student = auth('student')->user();

        // Activity base para “anclar” el intento in_progress
        $baseActivityId = $this->activityIdListening ?? $this->activityIdMatching ?? $this->activityIdQuiz;

        if (!$baseActivityId) {
            $this->state = 'flashcards';
            $this->flashIndex = 0;
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $attempt = StudentActivityAttempt::query()
            ->where('student_id', $student->id)
            ->where('activity_id', $baseActivityId)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        if (!$attempt) {
            $attempt = StudentActivityAttempt::create([
                'student_id' => $student->id,
                'activity_id' => $baseActivityId,
                'score_obtained' => 0,
                'max_score' => 0,
                'started_at' => now(),
                'status' => 'in_progress',
                'attempt_number' => 1,
            ]);
        }

        $this->activityAttemptId = $attempt->id;

        $this->state = 'flashcards';
        $this->flashIndex = 0;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- FLASHCARDS ----------------

    public function nextFlashcard(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->storeItemAttempt(
            itemKey: 'flash_' . $this->flashIndex,
            isCorrect: true,
            attempts: 1,
            hintsUsed: 0,
            response: [
                'type' => 'flashcard',
                'vocab_id' => $this->flashcards[$this->flashIndex]['id'] ?? null
            ]
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

    // ---------------- LISTENING ----------------

    public function pickListenOption(int $optIndex): void
    {
        if ($this->state !== 'listening') return;
        if ($this->listenLocked) return;

        $this->listenLocked = true;

        $item = $this->listenItems[$this->listenIndex] ?? null;
        if (!$item) {
            $this->goToMatching();
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
            $this->goToMatching();
            return;
        }

        $this->resetListenItemState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- MATCHING ----------------

    private function goToMatching(): void
    {
        $this->state = 'matching';
        $this->resetMatchState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function resetMatchState(): void
    {
        $this->matchSolved = [];
        $this->matchHiddenCards = [];
        $this->matchFirst = null;
        $this->matchSecond = null;
        $this->matchAttemptNo = 1;
        $this->matchHintsUsed = 0;
        $this->matchLocked = false;
        $this->matchFeedback = null;
    }

    /**
     * Matching “duplicado visual” (por ahora lo dejamos preparado, UI puede ser placeholder)
     * Cada vocab genera 2 cartas con el mismo pair_key.
     */
    private function buildMatchingFrom($vocabCollection): void
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

            // 2 cartas iguales (opción 1: duplicado visual)
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

        $this->matchPairs = $pairs;
        $this->matchCards = $cards;
    }

    /**
     * Placeholder: si no quieres UI de matching todavía,
     * este botón “Siguiente” lo llama tu vista.
     */
    public function nextMatching(): void
    {
        if ($this->state !== 'matching') return;

        $this->storeItemAttempt(
            itemKey: 'matching_done',
            isCorrect: true,
            attempts: 1,
            hintsUsed: 0,
            response: ['type' => 'matching', 'note' => 'placeholder']
        );

        $this->goToQuiz();
    }

    // ---------------- MULTIPLE CHOICE ----------------

    private function goToQuiz(): void
    {
        $this->state = 'multiple_choice';
        $this->quizIndex = 0;
        $this->resetQuizState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function resetQuizState(): void
    {
        $this->quizAttemptNo = 1;
        $this->quizHintsUsed = 0;
        $this->quizLocked = false;
        $this->quizFeedback = null;
    }

    public function pickQuizOption(int $optIndex): void
    {
        if ($this->state !== 'multiple_choice') return;
        if ($this->quizLocked) return;

        $this->quizLocked = true;

        $q = $this->quizQuestions[$this->quizIndex] ?? null;
        if (!$q) {
            $this->finishAttemptAsCompleted();
            $this->state = 'summary';
            return;
        }

        $options = $q['options'] ?? [];
        $picked = $options[$optIndex] ?? null;

        $correctId = (int)($q['correct_option_id'] ?? 0);
        $pickedId  = (int)($picked['id'] ?? 0);

        $isCorrect = $pickedId && $correctId && ($pickedId === $correctId);

        $this->storeItemAttempt(
            itemKey: 'mc_' . $this->quizIndex,
            isCorrect: $isCorrect,
            attempts: $this->quizAttemptNo,
            hintsUsed: $this->quizHintsUsed,
            response: [
                'type' => 'multiple_choice',
                'question_id' => $q['id'] ?? null,
                'picked_option_id' => $pickedId ?: null,
                'correct_option_id' => $correctId ?: null,
                'opt_index' => $optIndex,
                'attempt_no' => $this->quizAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->quizFeedback = 'correct';
            $this->advanceQuiz();
            return;
        }

        $this->quizFeedback = 'wrong';

        if ($this->quizAttemptNo === 1) {
            $this->quizHintsUsed += 1;
            $this->dispatch('quiz:shake');
        } elseif ($this->quizAttemptNo === 2) {
            $this->quizHintsUsed += 1;
            $this->dispatch('quiz:highlight-correct', correctId: $correctId);
        } else {
            $this->quizHintsUsed += 1;
            $this->dispatch('quiz:reveal-correct', correctId: $correctId);
        }

        $this->quizAttemptNo = min(3, $this->quizAttemptNo + 1);
        $this->quizLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function advanceQuiz(): void
    {
        $this->quizIndex++;

        if ($this->quizIndex >= count($this->quizQuestions)) {
            $this->finishAttemptAsCompleted();
            $this->state = 'summary';
            return;
        }

        $this->resetQuizState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function buildQuizFromActivity($quizActivity): void
    {
        $this->quizQuestions = [];

        if (!$quizActivity) return;

        $questions = $quizActivity->questions ?? collect();

        // hasta 5, por order_index si existe
        $questions = $questions->sortBy('order_index')->take(5);

        foreach ($questions as $q) {
            $opts = ($q->options ?? collect())->sortBy('order_index')->values();
            $correct = $opts->firstWhere('is_correct', true);

            $this->quizQuestions[] = [
                'id' => $q->id,
                'prompt' => $q->prompt ?? 'Elige la respuesta correcta',
                'correct_option_id' => $correct?->id,
                'options' => $opts->map(fn($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                ])->all(),
            ];
        }
    }

    // ---------------- SHARED HELPERS ----------------

    private function buildListenItemsFrom($vocabCollection): void
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

        $this->listenItems = $items;
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

        $items = StudentItemAttempt::query()
            ->where('activity_attempt_id', $this->activityAttemptId)
            ->get(['is_correct']);

        $max = $items->count();
        $score = $items->where('is_correct', true)->count();

        StudentActivityAttempt::whereKey($this->activityAttemptId)->update([
            'max_score' => $max,
            'score_obtained' => $score,
            'completed_at' => now(),
            'status' => 'completed',
        ]);
    }

    public function exitSession()
    {
        $this->finishAttemptAsAbandoned();
        return redirect()->route('student.lessons.index');
    }

    private function finishAttemptAsAbandoned(): void
    {
        if (!$this->activityAttemptId) return;

        StudentActivityAttempt::whereKey($this->activityAttemptId)->update([
            'completed_at' => now(),
            'status' => 'abandoned',
        ]);
    }

    public function render()
    {
        return view('livewire.student.session.player');
    }
}
