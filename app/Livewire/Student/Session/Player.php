<?php

namespace App\Livewire\Student\Session;

use Livewire\Component;
use Illuminate\Support\Collection;
use App\Models\ClassroomLessonAssignment;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;

class Player extends Component
{
    public int $assignmentId;

    // estados: intro | flashcards | listening | matching | multiple_choice | summary
    public string $state = 'intro';

    // ====== DATA BASE ======
    public array $flashcards = [];
    public array $listenItems = [];

    // Matching (duplicado visual hoy, luego iteras a imagen vs imagen / pedagógica)
    public array $matchCards = [];        // cartas mezcladas [{card_id,pair_key,image_path,label}]
    public array $matchSolved = [];       // card_id resueltas
    public array $matchHiddenCards = [];  // card_id ocultas (pista)
    public ?int $matchFirst = null;       // card_id
    public ?int $matchSecond = null;      // card_id
    public int $matchAttemptNo = 1;
    public int $matchHintsUsed = 0;
    public bool $matchLocked = false;
    public ?string $matchFeedback = null; // correct | wrong | null
    public ?string $matchPrompt = null;   // enunciado actual (word o translation)

    // Quiz
    public array $quizQuestions = [];  // [{id,prompt,options:[{id,text,image_path}],correct_option_id}]
    public int $quizIndex = 0;
    public int $quizAttemptNo = 1;
    public int $quizHintsUsed = 0;
    public bool $quizLocked = false;
    public ?string $quizFeedback = null;

    // ====== POINTERS ======
    public int $flashIndex = 0;
    public int $listenIndex = 0;

    public int $listenAttemptNo = 1;
    public int $listenHintsUsed = 0;
    public array $listenHidden = []; // vocab ids ocultos
    public bool $listenLocked = false;
    public ?string $lastFeedback = null;

    // ====== TRACKING ======
    public ?int $activityAttemptId = null;
    public ?int $itemStartedAtTs = null;

    // IDs de activities (opcional)
    public ?int $activityIdListening = null;
    public ?int $activityIdMatching  = null;
    public ?int $activityIdQuiz      = null;

    // cache interno
    private ClassroomLessonAssignment $assignment;
    private Collection $vocab;

    // ======================= MOUNT =======================
    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $this->assignment = ClassroomLessonAssignment::query()
            ->with([
                'lesson.vocabulary' => fn($q) => $q->where('status', 'published')
                    ->orderBy('lesson_vocabulary.order_index'),
                'lesson.activities.itemType',
                'lesson.activities.questions.options',
            ])
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active')
            ->findOrFail($assignmentId);

        $this->vocab = $this->assignment->lesson->vocabulary->values();

        // 1) flashcards
        $this->flashcards = $this->vocab->take(5)->map(fn($v) => [
            'id' => (int) $v->id,
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

        // 2) listening items (hasta 8)
        $this->listenItems = $this->buildListenItems($this->vocab->take(8));

        // 3) matching cards (hasta 6 vocab => 12 cartas)
        $this->matchCards = $this->buildMatchingCards($this->vocab->take(6));
        $this->resetMatchState(); // deja prompt listo

        // 4) resolver activities + quiz desde BD
        $this->resolveActivityIds();
        $this->quizQuestions = $this->buildQuizQuestions();

        // Si no hay listenItems, pasa a listening 
        if (empty($this->listenItems)) {
            $this->listenItems = [];
        }

        // Si no hay matchCards, queda vacío
        if (empty($this->matchCards)) {
            $this->matchCards = [];
            $this->matchPrompt = 'Empareja';
        }

        // Si no hay quizQuestions, queda vacío y el flujo debe saltar a summary
        if (empty($this->quizQuestions)) {
            $this->quizQuestions = [];
        }
    }

    // ======================= START =======================
    public function start(): void
    {
        $student = auth('student')->user();

        // anclamos el attempt al activity listening si existe (o al primero disponible)
        $baseActivityId = $this->activityIdListening ?? $this->activityIdMatching ?? $this->activityIdQuiz;

        if (!$baseActivityId) {
            $this->state = 'flashcards';
            $this->flashIndex = 0;
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        // reusar intento in_progress si existe
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

        $this->activityAttemptId = (int) $attempt->id;

        $this->state = 'flashcards';
        $this->flashIndex = 0;
        $this->itemStartedAtTs = now()->timestamp;
    }

    // ======================= FLASHCARDS =======================
    public function nextFlashcard(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->track(
            itemKey: 'flash_' . $this->flashIndex,
            isCorrect: true,
            attempts: 1,
            hintsUsed: 0,
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

    // ======================= LISTENING =======================
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

        $options = $item['options'] ?? [];
        $picked = $options[$optIndex] ?? null;
        $targetId = (int) ($item['target']['id'] ?? 0);
        $pickedId = (int) ($picked['id'] ?? 0);

        $isCorrect = $pickedId && ($pickedId === $targetId);

        $this->track(
            itemKey: 'listening_' . $this->listenIndex,
            isCorrect: $isCorrect,
            attempts: $this->listenAttemptNo,
            hintsUsed: $this->listenHintsUsed,
            response: [
                'type' => 'listening',
                'target_vocab_id' => $targetId ?: null,
                'picked_vocab_id' => $pickedId ?: null,
                'opt_index' => $optIndex,
                'attempt_no' => $this->listenAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->lastFeedback = 'correct';
            $this->advanceListening();
            return;
        }

        $this->lastFeedback = 'wrong';

        // Pistas
        if ($this->listenAttemptNo === 1) {
            $this->listenHintsUsed += 1;
            $this->dispatch('listen:play-audio');
        } elseif ($this->listenAttemptNo === 2) {
            $this->listenHintsUsed += 1;
            $this->applyListeningHideDistractors();
        } else {
            $this->listenHintsUsed += 1;
            $this->dispatch('listen:reveal-correct');
        }

        $this->listenAttemptNo = min(3, $this->listenAttemptNo + 1);
        $this->listenLocked = false;
        $this->itemStartedAtTs = now()->timestamp;
    }

    private function advanceListening(): void
    {
        $this->listenIndex++;

        if ($this->listenIndex >= count($this->listenItems)) {
            $this->goToMatching();
            return;
        }

        $this->resetListenState();
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

    private function applyListeningHideDistractors(): void
    {
        $item = $this->listenItems[$this->listenIndex] ?? null;
        if (!$item) return;

        $targetId = (int) ($item['target']['id'] ?? 0);

        $distractors = [];
        foreach (($item['options'] ?? []) as $opt) {
            $id = (int) ($opt['id'] ?? 0);
            if ($id && $id !== $targetId) $distractors[] = $id;
        }

        shuffle($distractors);
        $this->listenHidden = array_slice($distractors, 0, min(2, count($distractors)));
    }

    // ======================= MATCHING =======================
    private function goToMatching(): void
    {
        $this->state = 'matching';
        $this->resetMatchState();
        $this->itemStartedAtTs = now()->timestamp;
    }

    public function pickMatchCard(int $cardId): void
    {
        if ($this->state !== 'matching') return;
        if ($this->matchLocked) return;

        // ya resuelta u oculta
        if (in_array($cardId, $this->matchSolved, true)) return;
        if (in_array($cardId, $this->matchHiddenCards, true)) return;

        // seleccionar
        if ($this->matchFirst === null) {
            $this->matchFirst = $cardId;
            return;
        }

        if ($this->matchSecond === null && $cardId !== $this->matchFirst) {
            $this->matchSecond = $cardId;
            $this->evaluateMatch();
        }
    }

    private function evaluateMatch(): void
    {
        $first = $this->findCard($this->matchFirst);
        $second = $this->findCard($this->matchSecond);

        if (!$first || !$second) {
            $this->resetMatchPick();
            return;
        }

        $this->matchLocked = true;

        $isCorrect = ($first['pair_key'] === $second['pair_key']);

        $this->track(
            itemKey: 'matching_' . $this->matchPromptKey(),
            isCorrect: $isCorrect,
            attempts: $this->matchAttemptNo,
            hintsUsed: $this->matchHintsUsed,
            response: [
                'type' => 'matching',
                'prompt' => $this->matchPrompt,
                'first_card_id' => $this->matchFirst,
                'second_card_id' => $this->matchSecond,
                'first_pair_key' => $first['pair_key'],
                'second_pair_key' => $second['pair_key'],
                'attempt_no' => $this->matchAttemptNo,
            ]
        );

        if ($isCorrect) {
            $this->matchFeedback = 'correct';
            $this->matchSolved[] = $this->matchFirst;
            $this->matchSolved[] = $this->matchSecond;

            // siguiente enunciado
            $this->resetMatchPick();
            $this->matchLocked = false;
            $this->matchAttemptNo = 1; // reset attempts por prompt
            $this->matchHintsUsed = 0;
            $this->matchHiddenCards = [];
            $this->matchFeedback = null;

            if ($this->isMatchingCompleted()) {
                $this->goToQuiz();
                return;
            }

            $this->buildMatchPrompt();
            $this->itemStartedAtTs = now()->timestamp;
            return;
        }

        // wrong
        $this->matchFeedback = 'wrong';

        // pista por “resaltado del par correcto” en 2do intento
        if ($this->matchAttemptNo === 2) {
            $this->matchHintsUsed += 1;
            $pairKey = $first['pair_key']; // par del primer click
            $correctPair = $this->cardsByPairKey($pairKey);
            $this->dispatch('match:highlight-pair', cardIds: array_column($correctPair, 'card_id'));
        }

        $this->matchAttemptNo = min(3, $this->matchAttemptNo + 1);
        $this->matchLocked = false;

        // reset selección para reintentar (sin delay, UX simple)
        $this->resetMatchPick();
        $this->itemStartedAtTs = now()->timestamp;
    }

    public function matchHintHideTwoWrong(): void
    {
        if ($this->state !== 'matching') return;

        // oculta 2 cartas NO resueltas (pista simple)
        $candidates = collect($this->matchCards)
            ->filter(fn($c) => !in_array($c['card_id'], $this->matchSolved, true))
            ->filter(fn($c) => !in_array($c['card_id'], $this->matchHiddenCards, true))
            ->values();

        if ($candidates->count() <= 2) return;

        $pick = $candidates->shuffle()->take(2)->pluck('card_id')->all();
        foreach ($pick as $cid) $this->matchHiddenCards[] = (int) $cid;

        $this->matchHintsUsed += 1;
    }

    private function resetMatchState(): void
    {
        $this->matchSolved = [];
        $this->matchHiddenCards = [];
        $this->resetMatchPick();

        $this->matchAttemptNo = 1;
        $this->matchHintsUsed = 0;
        $this->matchLocked = false;
        $this->matchFeedback = null;

        $this->buildMatchPrompt();
    }

    private function resetMatchPick(): void
    {
        $this->matchFirst = null;
        $this->matchSecond = null;
    }

    private function buildMatchPrompt(): void
    {
        // “Enunciado”: elige un par todavía NO resuelto y usa su word/translation
        $unsolved = collect($this->matchCards)
            ->filter(fn($c) => !in_array($c['card_id'], $this->matchSolved, true))
            ->groupBy('pair_key')
            ->values();

        $group = $unsolved->first(); // estable (sin randomness salvaje)
        if (!$group || $group->count() === 0) {
            $this->matchPrompt = 'Encuentra el par';
            return;
        }

        $label = $group->first()['label'] ?? 'Encuentra el par';
        // aquí puedes cambiar a translation_es si quieres:
        $this->matchPrompt = "Busca: {$label}";
    }

    private function isMatchingCompleted(): bool
    {
        $total = count($this->matchCards);
        return count($this->matchSolved) >= $total && $total > 0;
    }

    private function matchPromptKey(): string
    {
        return (string) ($this->matchPrompt ?? 'prompt');
    }

    // ======================= QUIZ =======================
    private function goToQuiz(): void
    {
        $this->state = 'multiple_choice';
        $this->quizIndex = 0;
        $this->resetQuizState();
        $this->itemStartedAtTs = now()->timestamp;
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

        $correctId = (int) ($q['correct_option_id'] ?? 0);
        $pickedId  = (int) ($picked['id'] ?? 0);

        $isCorrect = $pickedId && $correctId && ($pickedId === $correctId);

        $this->track(
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

    private function resetQuizState(): void
    {
        $this->quizAttemptNo = 1;
        $this->quizHintsUsed = 0;
        $this->quizLocked = false;
        $this->quizFeedback = null;
    }

    // ======================= EXIT =======================
    public function exitSession()
    {
        $this->finishAttemptAsAbandoned();
        return redirect()->route('student.lessons.index');
    }

    // ======================= BUILDERS =======================
    private function resolveActivityIds(): void
    {
        $acts = $this->assignment->lesson->activities ?? collect();

        // Ajusta aquí si tu key es listen_choose en vez de listening
        $listening = $acts->first(fn($a) => optional($a->itemType)->key === 'listening')
            ?? $acts->first(fn($a) => optional($a->itemType)->key === 'listen_choose')
            ?? $acts->first();

        $matching = $acts->first(fn($a) => optional($a->itemType)->key === 'matching');
        $quiz     = $acts->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        $this->activityIdListening = $listening?->id ? (int)$listening->id : null;
        $this->activityIdMatching  = $matching?->id ? (int)$matching->id : null;
        $this->activityIdQuiz      = $quiz?->id ? (int)$quiz->id : null;
    }

    private function buildListenItems(Collection $vocab): array
    {
        $vocab = $vocab->values();

        $items = [];
        foreach ($vocab as $target) {
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->map(fn($v) => [
                'id' => (int) $v->id,
                'word_en' => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path' => $v->image_path,
                'audio_path' => $v->audio_path,
            ])->values()->all();

            $items[] = [
                'target' => [
                    'id' => (int) $target->id,
                    'word_en' => $target->word_en,
                    'audio_path' => $target->audio_path,
                ],
                'options' => $options,
            ];
        }

        return $items;
    }

    private function buildMatchingCards(Collection $vocab): array
    {
        $cards = [];
        $cardId = 1;

        foreach ($vocab->values() as $v) {
            $pairKey = 'v' . $v->id;

            // duplicado visual (2 cartas idénticas)
            $cards[] = [
                'card_id' => $cardId++,
                'pair_key' => $pairKey,
                'image_path' => $v->image_path,
                'label' => $v->word_en, // enunciado usa esto
            ];
            $cards[] = [
                'card_id' => $cardId++,
                'pair_key' => $pairKey,
                'image_path' => $v->image_path,
                'label' => $v->word_en,
            ];
        }

        shuffle($cards);
        return $cards;
    }

    private function buildQuizQuestions(): array
    {
        // 1) BD real si hay activity multiple_choice con questions/options válidas
        $acts = $this->assignment->lesson->activities ?? collect();
        $quizAct = $acts->first(fn($a) => optional($a->itemType)->key === 'multiple_choice');

        if ($quizAct && $quizAct->questions && $quizAct->questions->count() > 0) {
            $questions = $quizAct->questions->sortBy('order_index')->values()->take(5);

            $out = [];
            foreach ($questions as $q) {
                $opts = ($q->options ?? collect())->sortBy('order_index')->values();

                if ($opts->count() < 2) continue;

                $correct = $opts->firstWhere('is_correct', true);
                if (!$correct) continue;

                $out[] = [
                    'id' => (int) $q->id,
                    'prompt' => $q->prompt ?: 'Elige la respuesta correcta',
                    'correct_option_id' => (int) $correct->id,
                    'options' => $opts->map(fn($o) => [
                        'id' => (int) $o->id,
                        'text' => $o->text,
                        'image_path' => $o->image_path,
                    ])->all(),
                ];
            }

            if (!empty($out)) return $out;
        }

        // 2) fallback con vocab
        $vocab = $this->vocab->values();
        $out = [];

        foreach ($vocab->take(5) as $target) {
            $pool = $vocab->where('id', '!=', $target->id)->shuffle()->take(3)->values();

            $options = $pool->push($target)->shuffle()->values()->map(fn($v) => [
                'id' => (int) $v->id,
                'text' => $v->word_en,
                'image_path' => $v->image_path,
            ])->all();

            $out[] = [
                'id' => (int) $target->id,
                'prompt' => "¿Cuál es: {$target->translation_es}?",
                'correct_option_id' => (int) $target->id,
                'options' => $options,
            ];
        }

        return $out;
    }

    // ======================= CARD HELPERS =======================
    private function findCard(?int $cardId): ?array
    {
        if (!$cardId) return null;

        foreach ($this->matchCards as $c) {
            if ((int)$c['card_id'] === (int)$cardId) return $c;
        }
        return null;
    }

    private function cardsByPairKey(string $pairKey): array
    {
        return array_values(array_filter($this->matchCards, fn($c) => $c['pair_key'] === $pairKey));
    }

    // ======================= TRACKING HELPERS =======================
    private function track(string $itemKey, bool $isCorrect, int $attempts, int $hintsUsed, array $response): void
    {
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

    private function finishAttemptAsAbandoned(): void
    {
        if (!$this->activityAttemptId) return;

        StudentActivityAttempt::whereKey($this->activityAttemptId)->update([
            'completed_at' => now(),
            'status' => 'abandoned',
        ]);
    }

    // ======================= RENDER =======================
    public function render()
    {
        return view('livewire.student.session.player');
    }
}
