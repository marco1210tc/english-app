<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\Gate;

class AttemptDetail extends Component
{
    public Classroom $classroom;
    public StudentActivityAttempt $attempt;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    // filtros simples (opcionales)
    public string $filterCorrect = 'all'; // all|correct|wrong
    public string $search = '';
    public string $game = 'all'; // all|listening|matching|multiple_choice|flashcard|other

    public array $summary = [
        'count' => 0,
        'correct' => 0,
        'wrong' => 0,
        'hints' => 0,
        'seconds' => 0,
    ];

    public function mount(Classroom $classroom, StudentActivityAttempt $attempt): void
    {
        $this->classroom = $classroom;
        $this->attempt = $attempt;

        Gate::authorize('manage', $this->classroom);

        // seguridad: el attempt debe pertenecer a un estudiante de este classroom
        $this->attempt->loadMissing(['student:id,classroom_id,first_name,last_name,code', 'activity.lesson:id,title']);

        abort_unless(
            (int)($this->attempt->student?->classroom_id) === (int)$this->classroom->id,
            404
        );

        $this->loadItems();
    }

    public function updatedFilterCorrect(): void
    {
        $this->loadItems();
    }

    public function updatedSearch(): void
    {
        $this->loadItems();
    }
    
    public function updatedGame(): void
    {
        $this->loadItems();
    }


    public function loadItems(): void
    {
        $q = StudentItemAttempt::query()
            ->where('activity_attempt_id', $this->attempt->id)
            ->orderBy('id');

        if ($this->filterCorrect === 'correct') {
            $q->where('is_correct', true);
        } elseif ($this->filterCorrect === 'wrong') {
            $q->where('is_correct', false);
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            // buscamos por item_key o por contenido JSON (simple)
            $q->where(function ($qq) use ($term) {
                $qq->where('item_key', 'like', $term)
                    ->orWhere('response_json', 'like', $term);
            });
        }

        $items = $q->get([
            'id',
            'item_key',
            'is_correct',
            'attempts',
            'time_spent_seconds',
            'hints_used',
            'response_json',
            'created_at',
        ]);

        $this->items = $items->map(function ($i) {
            return [
                'id' => $i->id,
                'item_key' => $i->item_key,
                'is_correct' => (bool)$i->is_correct,
                'attempts' => (int)$i->attempts,
                'time_spent_seconds' => (int)$i->time_spent_seconds,
                'hints_used' => (int)$i->hints_used,
                'response_json' => $i->response_json, // cast a array
                'created_at' => $i->created_at,
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.attempt-detail');
    }
}
