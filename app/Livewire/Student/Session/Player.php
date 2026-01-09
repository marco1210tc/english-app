<?php

namespace App\Livewire\Student\Session;

use Livewire\Component;
use App\Services\StudentSession\SessionContentBuilder;
use App\Services\StudentSession\SessionTracker;

class Player extends Component
{
    public int $assignmentId;

    public string $state = 'intro';

    // flash
    public int $flashIndex = 0;
    public array $flashcards = [];

    // listening
    public int $listenIndex = 0;
    public array $listenItems = [];
    public int $listenAttemptNo = 1;
    public int $listenHintsUsed = 0;
    public array $listenHidden = []; // vocab ids a ocultar
    public bool $listenLocked = false;
    public ?string $lastFeedback = null;

    // matching
    public array $matchPairs = [];
    public array $matchCards = [];
    public array $matchSolved = [];       // card_ids resueltos
    public array $matchHiddenCards = [];  // card_ids ocultos (pista)
    public ?int $matchFirst = null;
    public ?int $matchSecond = null;
    public int $matchAttemptNo = 1;
    public int $matchHintsUsed = 0;
    public bool $matchLocked = false;
    public ?string $matchFeedback = null; // correct|wrong|null
    public array $matchHintPair = []; // card_ids a resaltar (pista)

    // quiz
    public array $quizQuestions = [];
    public int $quizIndex = 0;
    public int $quizAttemptNo = 1;
    public int $quizHintsUsed = 0;
    public bool $quizLocked = false;
    public ?string $quizFeedback = null; // correct|wrong|null

    // tracking
    public ?int $activityAttemptId = null;
    public ?int $itemStartedAtTs = null;

    // activity ids (por item_type.key)
    public ?int $activityIdListening = null;
    public ?int $activityIdMatching  = null;
    public ?int $activityIdQuiz      = null;

    // cached
    private $assignment;
    private $vocab;
    private SessionContentBuilder $builder;
    private SessionTracker $tracker;

    public function boot(SessionContentBuilder $builder, SessionTracker $tracker): void
    {
        $this->builder = $builder;
        $this->tracker = $tracker;
    }

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $this->assignment = $this->builder->loadAssignmentForStudent(
            assignmentId: $assignmentId,
            classroomId: (int) $student->classroom_id
        );

        $this->vocab = $this->assignment->lesson->vocabulary ?? collect();

        $this->flashcards  = $this->builder->buildFlashcards($this->vocab, 5);
        $this->listenItems = $this->builder->buildListeningItems($this->vocab, 8);

        [$pairs, $cards] = $this->builder->buildMatching($this->vocab, 6);
        $this->matchPairs = $pairs;
        $this->matchCards = $cards;

        $activities = $this->assignment->lesson->activities ?? collect();

        $listening = $activities->first(fn($a) => optional($a->itemType)->key === 'listening') ?? $activities->first();
        $matching  = $activities->first(fn($a) => optional($a->itemType)->key === 'matching');
        $quiz      = $activities->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        $this->activityIdListening = $listening?->id;
        $this->activityIdMatching  = $matching?->id;
        $this->activityIdQuiz      = $quiz?->id;

        $this->quizQuestions = $this->builder->buildQuizQuestions($quiz, $this->vocab, 5);
    }

    public function start(): void
    {
        $student = auth('student')->user();

        $baseActivityId = $this->activityIdListening ?? $this->activityIdMatching ?? $this->activityIdQuiz;

        if (!$baseActivityId) {
            $this->state = 'flashcards';
            $this->flashIndex = 0;
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $attempt = $this->tracker->startOrResumeAttempt((int)$student->id, (int)$baseActivityId);
        $this->activityAttemptId = $attempt->id;

        $this->state = 'flashcards';
        $this->flashIndex = 0;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- FLASHCARDS ----------------

    public function nextFlashcard(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->track(
            itemKey: 'flash_' . $this->flashIndex,
            isCorrect: true,
            attempts: 1,
            hints: 0,
            response: [
                'type' => 'flashcard',
                'vocab_id' => $this->flashcards[$this->flashIndex]['id'] ?? null,
            ]
        );

        $this->flashIndex++;

        if ($this->flashIndex >= count($this->flashcards)) {
            $this->goToListening();
            return;
        }

        $this->itemStartedAtTs = now()->timestamp;
    }

    private function goToListening(): void
    {
        $this->state = 'listening';
        $this->listenIndex = 0;
        $this->resetListenState();
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

        $picked = ($item['options'] ?? [])[$optIndex] ?? null;
        $isCorrect = $picked && ((int)$picked['id'] === (int)($item['target']['id'] ?? 0));

        $this->track(
            itemKey: 'listening_' . $this->listenIndex,
            isCorrect: $isCorrect,
            attempts: $this->listenAttemptNo,
            hints: $this->listenHintsUsed,
            response: [
                'type' => 'listening',
                'target_vocab_id' => $item['target']['id'] ?? null,
                'picked_vocab_id' => $picked['id'] ?? null,
                'opt_index' => $optIndex,
                'attempt_no' => $this->listenAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->lastFeedback = 'correct';
            $this->listenIndex++;
            if ($this->listenIndex >= count($this->listenItems)) {
                $this->goToMatching();
                return;
            }
            $this->resetListenState();
            $this->itemStartedAtTs = now()->timestamp;
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

    private function resetListenState(): void
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
        $this->matchHintPair = [];
    }

    public function pickMatchCard(int $cardId): void
    {
        if ($this->state !== 'matching') return;
        if ($this->matchLocked) return;
        if (in_array($cardId, $this->matchSolved, true)) return;
        if (in_array($cardId, $this->matchHiddenCards, true)) return;

        // si ya está seleccionada como first, no hacer nada
        if ($this->matchFirst === $cardId) return;

        if ($this->matchFirst === null) {
            $this->matchFirst = $cardId;
            $this->matchFeedback = null;
            return;
        }

        // segunda carta
        $this->matchSecond = $cardId;
        $this->matchLocked = true;

        $first  = $this->findCard($this->matchFirst);
        $second = $this->findCard($this->matchSecond);

        $isCorrect = $first && $second && ($first['pair_key'] === $second['pair_key']);

        $this->track(
            itemKey: 'matching_' . $this->matchFirst . '_' . $this->matchSecond,
            isCorrect: $isCorrect,
            attempts: $this->matchAttemptNo,
            hints: $this->matchHintsUsed,
            response: [
                'type' => 'matching',
                'first_card_id' => $this->matchFirst,
                'second_card_id' => $this->matchSecond,
                'pair_key_first' => $first['pair_key'] ?? null,
                'pair_key_second' => $second['pair_key'] ?? null,
            ]
        );

        if ($isCorrect) {
            $this->matchFeedback = 'correct';
            $this->matchSolved[] = $this->matchFirst;
            $this->matchSolved[] = $this->matchSecond;

            $this->resetMatchSelection();

            // completo
            if (count($this->matchSolved) >= count($this->matchCards)) {
                $this->goToQuiz();
                return;
            }

            $this->matchLocked = false;
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        // incorrecto: pista por intentos
        $this->matchFeedback = 'wrong';

        if ($this->matchAttemptNo === 1) {
            $this->matchHintsUsed += 1;
            $this->dispatch('match:shake');
        } elseif ($this->matchAttemptNo === 2) {
            $this->matchHintsUsed += 1;
            $this->hintMatchingPairForSelected();
        } else {
            $this->matchHintsUsed += 1;
            $this->dispatch('match:hint');
        }

        $this->matchAttemptNo = min(3, $this->matchAttemptNo + 1);

        // desbloquea y limpia selección después de “mostrar”
        $this->resetMatchSelection();
        $this->matchLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function resetMatchSelection(): void
    {
        $this->matchFirst = null;
        $this->matchSecond = null;
    }

    private function findCard(int $cardId): ?array
    {
        foreach ($this->matchCards as $c) {
            if ((int)$c['card_id'] === (int)$cardId) return $c;
        }
        return null;
    }

    private function hintMatchingPairForSelected(): void
    {
        if ($this->matchFirst === null) return;

        $first = $this->findCard($this->matchFirst);
        if (!$first) return;

        $pairKey = $first['pair_key'] ?? null;
        if (!$pairKey) return;

        $ids = collect($this->matchCards)
            ->filter(
                fn($c) => ($c['pair_key'] ?? null) === $pairKey &&
                    !in_array((int)$c['card_id'], $this->matchSolved, true)
            )
            ->pluck('card_id')
            ->map(fn($x) => (int)$x)
            ->values()
            ->all();

        $this->matchHintPair = $ids; // normalmente 2 ids
    }


    // ---------------- QUIZ ----------------

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
            $this->finishAndGoSummary();
            return;
        }

        $options = $q['options'] ?? [];
        $picked  = $options[$optIndex] ?? null;

        $correctId = (int)($q['correct_option_id'] ?? 0);
        $pickedId  = (int)($picked['id'] ?? 0);

        $isCorrect = $pickedId && $correctId && ($pickedId === $correctId);

        $this->track(
            itemKey: 'mc_' . $this->quizIndex,
            isCorrect: $isCorrect,
            attempts: $this->quizAttemptNo,
            hints: $this->quizHintsUsed,
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
            $this->quizIndex++;

            if ($this->quizIndex >= count($this->quizQuestions)) {
                $this->finishAndGoSummary();
                return;
            }

            $this->resetQuizState();
            $this->itemStartedAtTs = now()->timestamp;
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

    private function finishAndGoSummary(): void
    {
        if ($this->activityAttemptId) {
            $this->tracker->finishCompleted($this->activityAttemptId);
        }
        $this->state = 'summary';
    }

    // ---------------- SHARED ----------------

    private function track(string $itemKey, bool $isCorrect, int $attempts, int $hints, array $response): void
    {
        if (!$this->activityAttemptId) return;

        $this->tracker->storeItemAttempt(
            activityAttemptId: $this->activityAttemptId,
            itemKey: $itemKey,
            isCorrect: $isCorrect,
            attempts: $attempts,
            hintsUsed: $hints,
            response: $response,
            itemStartedAtTs: $this->itemStartedAtTs
        );

        // reinicia timer por defecto
        $this->itemStartedAtTs = now()->timestamp;
    }

    public function exitSession()
    {
        if ($this->activityAttemptId) {
            $this->tracker->finishAbandoned($this->activityAttemptId);
        }
        return redirect()->route('student.lessons.index');
    }

    public function render()
    {
        return view('livewire.student.session.player');
    }
}
