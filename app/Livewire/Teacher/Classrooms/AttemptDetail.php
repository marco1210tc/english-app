<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

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

        // filtro por juego (type en response_json) - compatible sqlite/mysql
        $this->applyGameFilter($q);

        // correct/wrong
        if ($this->filterCorrect === 'correct') {
            $q->where('is_correct', true);
        } elseif ($this->filterCorrect === 'wrong') {
            $q->where('is_correct', false);
        }

        // búsqueda
        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
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

        // summary del filtro actual
        $count = $items->count();
        $correct = $items->where('is_correct', true)->count();
        $wrong = $count - $correct;
        $hints = (int) $items->sum('hints_used');
        $seconds = (int) $items->sum('time_spent_seconds');

        $this->summary = [
            'count' => $count,
            'correct' => $correct,
            'wrong' => $wrong,
            'hints' => $hints,
            'seconds' => $seconds,
        ];

        $this->items = $items->map(function ($i) {
            $type = null;
            if (is_array($i->response_json)) {
                $type = $i->response_json['type'] ?? null;
            }

            return [
                'id' => $i->id,
                'type' => $type ?: '—',
                'item_key' => $i->item_key,
                'is_correct' => (bool)$i->is_correct,
                'attempts' => (int)$i->attempts,
                'time_spent_seconds' => (int)$i->time_spent_seconds,
                'hints_used' => (int)$i->hints_used,
                'response_json' => $i->response_json,
                'created_at' => $i->created_at,
            ];
        })->all();
    }


    private function applyGameFilter($q): void
    {
        if ($this->game === 'all') return;

        $driver = $q->getConnection()->getDriverName(); // sqlite|mysql|pgsql...

        if ($driver === 'sqlite') {
            if ($this->game === 'other') {
                $q->where(function ($qq) {
                    $qq->whereNull('response_json')
                        ->orWhereRaw("json_extract(response_json, '$.type') IS NULL")
                        ->orWhereRaw("json_extract(response_json, '$.type') NOT IN ('listening','matching','multiple_choice','flashcard')");
                });
            } else {
                $q->whereRaw("json_extract(response_json, '$.type') = ?", [$this->game]);
            }
            return;
        }

        // MySQL / MariaDB
        if ($this->game === 'other') {
            $q->where(function ($qq) {
                $qq->whereNull('response_json')
                    ->orWhereRaw("JSON_EXTRACT(response_json, '$.type') IS NULL")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(response_json, '$.type')) NOT IN ('listening','matching','multiple_choice','flashcard')");
            });
        } else {
            $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(response_json, '$.type')) = ?", [$this->game]);
        }
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.attempt-detail');
    }
}
