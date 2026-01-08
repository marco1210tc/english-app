<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\ClassroomLessonAssignment;
use App\Models\StudentActivityAttempt;

class Dashboard extends Component
{
    public string $studentName = 'estudiante';

    // Bloque 1: continuar
    public ?int $continueAssignmentId = null;
    public ?string $continueLessonTitle = null;
    public ?string $continueModuleTitle = null;

    // Bloque 2: vence pronto
    public $dueSoon = [];

    // Bloque 3: resumen
    public array $stats = [
        'pending' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'total' => 0,
    ];

    // Progreso general (por ahora derivado del resumen)
    public int $overallProgress = 0;

    public function mount(): void
    {
        $student = auth('student')->user();
        $this->studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        $this->studentName = $this->studentName !== '' ? $this->studentName : 'estudiante';

        // ---------- CONTINUAR: último intento in_progress ----------
        $inProgressAttempt = StudentActivityAttempt::query()
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->with(['activity.lesson.module'])
            ->latest('id')
            ->first();

        if ($inProgressAttempt && $inProgressAttempt->activity?->lesson) {
            $lesson = $inProgressAttempt->activity->lesson;

            $assignment = ClassroomLessonAssignment::query()
                ->where('classroom_id', $student->classroom_id)
                ->where('lesson_id', $lesson->id)
                ->where('status', 'active')
                ->first();

            if ($assignment) {
                $this->continueAssignmentId = $assignment->id;
                $this->continueLessonTitle = $lesson->title;
                $this->continueModuleTitle = $lesson->module->title ?? null;
            }
        }

        // Fallback: próxima lección activa por fecha
        if (!$this->continueAssignmentId) {
            $next = ClassroomLessonAssignment::query()
                ->with(['lesson.module'])
                ->where('classroom_id', $student->classroom_id)
                ->where('status', 'active')
                ->orderByRaw('due_at IS NULL ASC')
                ->orderBy('due_at', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($next) {
                $this->continueAssignmentId = $next->id;
                $this->continueLessonTitle = $next->lesson->title ?? 'Lección';
                $this->continueModuleTitle = $next->lesson->module->title ?? null;
            }
        }

        // ---------- VENCE PRONTO (top 4 con due_at) ----------
        $this->dueSoon = ClassroomLessonAssignment::query()
            ->with(['lesson.module'])
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active')
            ->whereNotNull('due_at')
            ->orderBy('due_at', 'asc')
            ->limit(4)
            ->get();

        // ---------- RESUMEN ----------
        $assignments = ClassroomLessonAssignment::query()
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active')
            ->get(['id', 'lesson_id']);

        $lessonIds = $assignments->pluck('lesson_id')->unique()->values();

        $attempts = StudentActivityAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->whereHas('activity', fn($q) => $q->whereIn('lesson_id', $lessonIds))
            ->with(['activity:id,lesson_id'])
            ->get(['id','activity_id','status']);

        $lessonHasCompleted = [];
        $lessonHasInProgress = [];

        foreach ($attempts as $a) {
            $lid = $a->activity?->lesson_id;
            if (!$lid) continue;

            if ($a->status === 'completed') $lessonHasCompleted[$lid] = true;
            if ($a->status === 'in_progress') $lessonHasInProgress[$lid] = true;
        }

        $completed = 0;
        $inProgress = 0;
        $pending = 0;

        foreach ($assignments as $as) {
            $lid = $as->lesson_id;

            if (!empty($lessonHasCompleted[$lid])) { $completed++; continue; }
            if (!empty($lessonHasInProgress[$lid])) { $inProgress++; continue; }
            $pending++;
        }

        $total = $assignments->count();

        $this->stats = [
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'total' => $total,
        ];

        // Progreso simple: completadas / total
        $this->overallProgress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    public function goContinue()
    {
        if (!$this->continueAssignmentId) return redirect()->route('student.lessons.index');

        return redirect()->route('student.session.play', $this->continueAssignmentId);
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
