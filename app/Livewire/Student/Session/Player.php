<?php

namespace App\Livewire\Student\Session;

use Livewire\Component;
use App\Models\ClassroomLessonAssignment;

class Player extends Component
{
    public int $assignmentId;

    public string $state = 'intro'; // intro | flashcards | summary
    public int $index = 0;

    /** @var array<int, array{word_en:string,translation_es:string,image_path:?string,audio_path:?string}> */
    public array $flashcards = [];

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;

        $student = auth('student')->user();

        $assignment = ClassroomLessonAssignment::query()
            ->with(['lesson.vocabulary' => function ($q) {
                // MVP: solo publicado
                $q->where('status', 'published');
            }])
            ->where('classroom_id', $student->classroom_id)
            ->findOrFail($assignmentId);

        // Tomamos 3–5 para micro-sesión
        $this->flashcards = $assignment->lesson
            ->vocabulary
            ->take(5)
            ->map(fn ($v) => [
                'word_en' => $v->word_en,
                'translation_es' => $v->translation_es,
                'image_path' => $v->image_path,
                'audio_path' => $v->audio_path,
            ])
            ->values()
            ->all();

        // Fallback: si el docente aún no publicó vocab, no rompemos UX
        if (count($this->flashcards) === 0) {
            $this->flashcards = [[
                'word_en' => 'No items',
                'translation_es' => 'Sin vocabulario publicado',
                'image_path' => null,
                'audio_path' => null,
            ]];
        }
    }

    public function start(): void
    {
        // TODO próximo: crear student_activity_attempts aquí
        $this->state = 'flashcards';
        $this->index = 0;
    }

    public function next(): void
    {
        if ($this->state !== 'flashcards') return;

        $this->index++;

        if ($this->index >= count($this->flashcards)) {
            $this->state = 'summary';
            $this->index = 0;

            // TODO próximo: finishAttempt()
        }
    }

    public function render()
    {
        return view('livewire.student.session.player');
    }
}
