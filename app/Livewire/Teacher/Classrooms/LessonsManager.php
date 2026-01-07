<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\ClassroomLessonAssignment;
use Illuminate\Support\Facades\Gate;

class LessonsManager extends Component
{
    public Classroom $classroom;

    /** @var \Illuminate\Support\Collection */
    public $lessons;

    /** asignaciones existentes indexadas por lesson_id */
    public array $assigned = []; // [lesson_id => ['id'=>..,'due_at'=>..,'status'=>..]]

    /** form: selección y due */
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
        // 1) lecciones del grado de la sección
        $this->lessons = Lesson::query()
            ->with('module:id,title,grade_id') // ajusta title si tu modules usa otro campo
            ->whereHas('module', fn($q) => $q->where('grade_id', $this->classroom->grade_id))
            ->orderBy('module_id')
            ->orderBy('order_index')
            ->get();

        // 2) asignaciones actuales
        $assignments = ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->get();

        $this->assigned = [];
        foreach ($assignments as $a) {
            $this->assigned[$a->lesson_id] = [
                'id' => $a->id,
                'due_at' => optional($a->due_at)->format('Y-m-d\TH:i') ?? null,
                'status' => $a->status,
            ];
        }

        // 3) prefill form (solo si no estaba tocado)
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

        foreach ($this->select as $lessonId => $checked) {
            $lessonId = (int) $lessonId;

            if (!$checked) continue;

            // upsert
            ClassroomLessonAssignment::updateOrCreate(
                [
                    'classroom_id' => $this->classroom->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'due_at' => $this->dueAt[$lessonId] ?? null,
                    'status' => 'active',
                ]
            );
        }

        $this->loadData();
        $this->dispatch('toast', message: 'Lecciones asignadas.');
    }

    public function updateDueAt(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        $a = ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->where('lesson_id', $lessonId)
            ->first();

        if (!$a) return;

        $a->due_at = $this->dueAt[$lessonId] ?? null;
        $a->save();

        $this->loadData();
    }

    public function toggleStatus(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        $a = ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->where('lesson_id', $lessonId)
            ->first();

        if (!$a) return;

        $a->status = $a->status === 'active' ? 'closed' : 'active';
        $a->save();

        $this->loadData();
    }

    public function unassign(int $lessonId): void
    {
        Gate::authorize('manage', $this->classroom);

        ClassroomLessonAssignment::query()
            ->where('classroom_id', $this->classroom->id)
            ->where('lesson_id', $lessonId)
            ->delete();

        unset($this->select[$lessonId], $this->dueAt[$lessonId]);

        $this->loadData();
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.lessons-manager');
    }
}