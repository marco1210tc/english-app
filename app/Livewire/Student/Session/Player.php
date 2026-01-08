<?php

namespace App\Livewire\Student\Session;

use Livewire\Component;
use App\Services\StudentSession\SessionContentBuilder;
use App\Services\StudentSession\AttemptTracker;
use App\Services\StudentSession\Games\ListeningGame;
use App\Services\StudentSession\Games\MatchingGame;
use App\Services\StudentSession\Games\MultipleChoiceGame;

class Player extends Component
{
    public int $assignmentId;

    public string $state = 'intro';

    // FLASHgit 
    public int $flashIndex = 0;
    public array $flashcards = [];

    // LISTENING
    public int $listenIndex = 0;
    public array $listenItems = [];
    public int $listenAttemptNo = 1;
    public int $listenHintsUsed = 0;
    public array $listenHidden = [];
    public bool $listenLocked = false;
    public ?string $lastFeedback = null;

    // MATCHING
    public array $matchPairs = [];
    public array $matchCards = [];
    public array $matchSolved = [];
    public array $matchHiddenCards = [];
    public ?int $matchFirst = null;
    public ?int $matchSecond = null;
    public int $matchAttemptNo = 1;
    public int $matchHintsUsed = 0;
    public bool $matchLocked = false;
    public ?string $matchFeedback = null;

    // QUIZ
    public array $quizQuestions = [];
    public int $quizIndex = 0;
    public int $quizAttemptNo = 1;
    public int $quizHintsUsed = 0;
    public bool $quizLocked = false;
    public ?string $quizFeedback = null;

    // Tracking
    public ?int $activityIdListening = null;
    public ?int $activityIdMatching  = null;
    public ?int $activityIdQuiz      = null;

    public ?int $activityAttemptId = null;
    public ?int $itemStartedAtTs = null;

    // cache assignment
    private $assignment;

    // services
    private SessionContentBuilder $builder;
    private AttemptTracker $tracker;
    private ListeningGame $listeningGame;
    private MatchingGame $matchingGame;
    private MultipleChoiceGame $mcGame;

    public function boot(
        SessionContentBuilder $builder,
        AttemptTracker $tracker,
        ListeningGame $listeningGame,
        MatchingGame $matchingGame,
        MultipleChoiceGame $mcGame
    ): void {
        $this->builder = $builder;
        $this->tracker = $tracker;
        $this->listeningGame = $listeningGame;
        $this->matchingGame = $matchingGame;
        $this->mcGame = $mcGame;
    }

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $this->assignment = $this->builder->loadAssignmentForStudent(
            assignmentId: $assignmentId,
            studentClassroomId: (int)$student->classroom_id
        );

        $lesson = $this->assignment->lesson;
        $vocab = $lesson->vocabulary;

        // flashcards
        $this->flashcards = $this->builder->buildFlashcards($vocab, 5);

        // listening
        $this->listenItems = $this->listeningGame->buildItems($vocab->take(8));

        // matching (6 vocab -> 12 cards)
        $m = $this->matchingGame->buildFromVocab($vocab->take(6));
        $this->matchPairs = $m['pairs'];
        $this->matchCards = $m['cards'];

        // activity ids
        $ids = $this->builder->resolveActivityIds($lesson);
        $this->activityIdListening = $ids['listening'];
        $this->activityIdMatching  = $ids['matching'];
        $this->activityIdQuiz      = $ids['quiz'];

        // quiz questions
        $quizActivity = $lesson->activities?->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');
        $this->quizQuestions = $this->mcGame->buildFromActivity($quizActivity);
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

        $attempt = $this->tracker->startOrReuseAttempt((int)$student->id, (int)$baseActivityId);
        $this->activityAttemptId = (int)$attempt->id;

        $this->state = 'flashcards';
        $this->flashIndex = 0;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- shared tracking helper ----------------

    private function secondsSinceStart(): int
    {
        if (!$this->itemStartedAtTs) return 0;
        return max(0, now()->timestamp - $this->itemStartedAtTs);
    }

    private function track(string $itemKey, bool $isCorrect, int $attempts, int $hintsUsed, array $response): void
    {
        if (!$this->activityAttemptId) return;

        $this->tracker->storeItemAttempt(
            activityAttemptId: (int)$this->activityAttemptId,
            itemKey: $itemKey,
            isCorrect: $isCorrect,
            attempts: $attempts,
            hintsUsed: $hintsUsed,
            timeSpentSeconds: $this->secondsSinceStart(),
            response: $response
        );
    }

    // ---------------- FLASH ----------------

    public function nextFlashcard(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->track(
            itemKey: 'flash_'.$this->flashIndex,
            isCorrect: true,
            attempts: 1,
            hintsUsed: 0,
            response: ['type' => 'flashcard', 'vocab_id' => $this->flashcards[$this->flashIndex]['id'] ?? null]
        );

        $this->flashIndex++;

        if ($this->flashIndex >= count($this->flashcards)) {
            $this->state = 'listening';
            $this->listenIndex = 0;
            $this->resetListeningState();
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- LISTENING ----------------

    private function resetListeningState(): void
    {
        $this->listenAttemptNo = 1;
        $this->listenHintsUsed = 0;
        $this->listenHidden = [];
        $this->listenLocked = false;
        $this->lastFeedback = null;
    }

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
            itemKey: 'listening_'.$this->listenIndex,
            isCorrect: $isCorrect,
            attempts: $this->listenAttemptNo,
            hintsUsed: $this->listenHintsUsed,
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

            $this->resetListeningState();
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $this->lastFeedback = 'wrong';

        if ($this->listenAttemptNo === 1) {
            $this->listenHintsUsed++;
            $this->dispatch('listen:play-audio');
        } elseif ($this->listenAttemptNo === 2) {
            $this->listenHintsUsed++;
            $this->listenHidden = $this->listeningGame->hideDistractors($item);
        } else {
            $this->listenHintsUsed++;
            $this->dispatch('listen:reveal-correct');
        }

        $this->listenAttemptNo = min(3, $this->listenAttemptNo + 1);
        $this->listenLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- MATCHING ----------------

    private function goToMatching(): void
    {
        $this->state = 'matching';
        $this->resetMatchingState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function resetMatchingState(): void
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

    public function pickMatchCard(int $cardId): void
    {
        if ($this->state !== 'matching') return;
        if ($this->matchLocked) return;

        if (in_array($cardId, $this->matchSolved, true)) return;
        if (in_array($cardId, $this->matchHiddenCards, true)) return;
        if ($this->matchFirst === $cardId) return;

        if ($this->matchFirst === null) {
            $this->matchFirst = $cardId;
            return;
        }

        $this->matchSecond = $cardId;
        $this->matchLocked = true;

        $isCorrect = $this->matchingGame->pickCard($this->matchCards, (int)$this->matchFirst, (int)$this->matchSecond);

        $first = $this->matchingGame->findCard($this->matchCards, (int)$this->matchFirst);
        $second = $this->matchingGame->findCard($this->matchCards, (int)$this->matchSecond);
        $pairKey = $first['pair_key'] ?? ('card_'.$this->matchFirst);

        $this->track(
            itemKey: 'match_'.$pairKey,
            isCorrect: $isCorrect,
            attempts: $this->matchAttemptNo,
            hintsUsed: $this->matchHintsUsed,
            response: [
                'type' => 'matching',
                'pair_key' => $pairKey,
                'first_card_id' => $this->matchFirst,
                'second_card_id' => $this->matchSecond,
                'first_vocab_id' => $first['vocab_id'] ?? null,
                'second_vocab_id' => $second['vocab_id'] ?? null,
                'attempt_no' => $this->matchAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->matchFeedback = 'correct';
            $this->matchSolved[] = (int)$this->matchFirst;
            $this->matchSolved[] = (int)$this->matchSecond;

            $this->matchFirst = null;
            $this->matchSecond = null;
            $this->matchAttemptNo = 1;
            $this->matchLocked = false;
            $this->itemStartedAtTs = now()->timestamp;

            if (count($this->matchSolved) >= count($this->matchCards)) {
                $this->goToQuiz();
            }
            return;
        }

        $this->matchFeedback = 'wrong';

        if ($this->matchAttemptNo === 1) {
            $this->matchHintsUsed++;
        } elseif ($this->matchAttemptNo === 2) {
            $this->matchHintsUsed++;
            $this->matchHiddenCards = array_values(array_unique(array_merge(
                $this->matchHiddenCards,
                $this->matchingGame->hideDistractorCards($this->matchCards, $this->matchSolved, $this->matchFirst, $this->matchSecond)
            )));
        } else {
            $this->matchHintsUsed++;
        }

        $this->matchAttemptNo = min(3, $this->matchAttemptNo + 1);

        $this->dispatch('match:wrong');
        // desbloquea cuando el frontend llame resetWrongMatch()
    }

    public function resetWrongMatch(): void
    {
        if ($this->state !== 'matching') return;

        if ($this->matchFeedback === 'wrong') {
            $this->matchFirst = null;
            $this->matchSecond = null;
            $this->matchLocked = false;
            $this->itemStartedAtTs = now()->timestamp;
        }
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
            $this->finishCompleted();
            $this->state = 'summary';
            return;
        }

        $picked = ($q['options'] ?? [])[$optIndex] ?? null;
        $correctId = (int)($q['correct_option_id'] ?? 0);
        $pickedId = (int)($picked['id'] ?? 0);

        $isCorrect = $pickedId && $correctId && ($pickedId === $correctId);

        $this->track(
            itemKey: 'mc_'.$this->quizIndex,
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
            $this->quizIndex++;

            if ($this->quizIndex >= count($this->quizQuestions)) {
                $this->finishCompleted();
                $this->state = 'summary';
                return;
            }

            $this->resetQuizState();
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        $this->quizFeedback = 'wrong';

        if ($this->quizAttemptNo === 1) {
            $this->quizHintsUsed++;
            $this->dispatch('quiz:shake');
        } elseif ($this->quizAttemptNo === 2) {
            $this->quizHintsUsed++;
            $this->dispatch('quiz:highlight-correct', correctId: $correctId);
        } else {
            $this->quizHintsUsed++;
            $this->dispatch('quiz:reveal-correct', correctId: $correctId);
        }

        $this->quizAttemptNo = min(3, $this->quizAttemptNo + 1);
        $this->quizLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ---------------- FINISH / EXIT ----------------

    private function finishCompleted(): void
    {
        if (!$this->activityAttemptId) return;
        $this->tracker->finishAsCompleted((int)$this->activityAttemptId);
    }

    public function exitSession()
    {
        if ($this->activityAttemptId) {
            $this->tracker->finishAsAbandoned((int)$this->activityAttemptId);
        }
        return redirect()->route('student.lessons.index');
    }

    public function render()
    {
        return view('livewire.student.session.player');
    }
}
