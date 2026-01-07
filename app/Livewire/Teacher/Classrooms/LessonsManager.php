<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\ClassroomLessonAssignment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;

class LessonsManager extends Component
{
    public Classroom $classroom;

    public $lessons;

    public array $assigned = []; // [lesson_id => ['id'=>..,'due_at'=>..,'status'=>..]]
    public array $select = [];   // [lesson_id => true/false]
    public array $dueAt = [];    // [lesson_id => 'YYYY-MM-DDTHH:MM' | null]

    public function mount(Classroom $classroom): void
    {
        $this->classroom = $classroom;
        Gate::authorize('manage', $this->classroom);

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->lessons = Lesson::query()
            ->with('module:id,title,grade_id')
            ->whereHas('module', fn($q) => $q->where('grade_id', $this->classroom->grade_id))
            ->orderBy('module_id')
            ->orderBy('order_index')
            ->get();

        $assignments = ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->get();

        $this->assigned = [];
        foreach ($assignments as $a) {
            $this->assigned[$a->lesson_id] = [
                'id' => $a->id,
                'due_at' => $a->due_at ? Carbon::parse($a->due_at)->format('Y-m-d\TH:i') : null,
                'status' => $a->status,
            ];
        }

        foreach ($this->lessons as $l) {
            $id = $l->id;

            if (!array_key_exists($id, $this->select)) {
                $this->select[$id] = isset($this->assigned[$id]);
            }
            if (!array_key_exists($id, $this->dueAt)) {
                $this->dueAt[$id] = $this->assigned[$id]['due_at'] ?? null;
            }
        }
    }

    public function assignSelected(): void
    {
        Gate::authorize('manage', $this->classroom);

        // seguridad: lecciones válidas del grado
        $validLessonIds = $this->lessons->pluck('id')->map(fn($v) => (int)$v)->all();

        foreach ($this->select as $lessonId => $checked) {
            $lessonId = (int) $lessonId;

            if (!in_array($lessonId, $validLessonIds, true)) {
                continue; // ignora ids inválidos
            }

            $existing = ClassroomLessonAssignment::query()
                ->where('classroom_id', $this->classroom->id)
                ->where('lesson_id', $lessonId)
                ->first();

            // Si no está marcado y existe -> eliminar (sync)
            if (!$checked) {
                if ($existing) {
                    $existing->delete();
                }
                continue;
            }

            // Marcado -> upsert sin pisar assigned_at si ya existía
            $dueAt = $this->parseDueAt($this->dueAt[$lessonId] ?? null);

            if (!$existing) {
                ClassroomLessonAssignment::create([
                    'classroom_id' => $this->classroom->id,
                    'lesson_id' => $lessonId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'due_at' => $dueAt,
                    'status' => 'active',
                ]);
            } else {
                $existing->update([
                    'due_at' => $dueAt,
                    // NO tocamos assigned_at
                    // status se mantiene como esté
                ]);
            }
        }

        $this->resetTouchedState();
        $this->loadData();
        $this->dispatch('toast', message: 'Asignaciones guardadas.');
    }

    public function updateDueAt(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        if (!$this->isValidLesson($lessonId)) return;

        $a = $this->assignmentOrNull($lessonId);
        if (!$a) return;

        $a->due_at = $this->parseDueAt($this->dueAt[$lessonId] ?? null);
        $a->save();

        $this->loadData();
    }

    public function toggleStatus(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        if (!$this->isValidLesson($lessonId)) return;

        $a = $this->assignmentOrNull($lessonId);
        if (!$a) return;

        $a->status = $a->status === 'active' ? 'closed' : 'active';
        $a->save();

        $this->loadData();
    }

    public function unassign(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        if (!$this->isValidLesson($lessonId)) return;

        $a = $this->assignmentOrNull($lessonId);
        if ($a) $a->delete();

        $this->select[$lessonId] = false;
        $this->dueAt[$lessonId] = null;

        $this->loadData();
    }

    private function assignmentOrNull(int $lessonId): ?ClassroomLessonAssignment
    {
        return ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->where('lesson_id', $lessonId)
            ->first();
    }

    private function parseDueAt(?string $value): ?Carbon
    {
        if (!$value) return null;
        return Carbon::parse($value);
    }

    private function isValidLesson(int $lessonId): bool
    {
        return $this->lessons->contains(fn($l) => (int)$l->id === (int)$lessonId);
    }

    private function resetTouchedState(): void
    {
        // opcional: si quieres que al guardar vuelva a “refrescar” estados editados
        // aquí no hacemos reset total para no molestar UX; pero si quieres, lo activas.
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.lessons-manager');
    }
}
