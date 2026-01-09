<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentResults extends Component
{
    public Classroom $classroom;
    public Student $student;

    /** @var array<int, array<string, mixed>> */
    public array $attempts = [];

    public function mount(Classroom $classroom, Student $student): void
    {
        $this->classroom = $classroom;
        $this->student = $student;

        Gate::authorize('manage', $this->classroom);

        // seguridad: el estudiante debe pertenecer a la sección
        abort_unless((int)$this->student->classroom_id === (int)$this->classroom->id, 404);

        $this->loadAttempts();
    }

    public function loadAttempts(): void
    {
        // Traemos attempts completed del estudiante + activity + lesson (para título)
        $attempts = StudentActivityAttempt::query()
            ->with(['activity.lesson:id,title']) // ajusta si lesson tiene otro campo
            ->where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->get([
                'id',
                'activity_id',
                'score_obtained',
                'max_score',
                'started_at',
                'completed_at',
                'created_at',
            ]);

        $attemptIds = $attempts->pluck('id')->all();

        // Tiempo total por attempt (sum item_attempts.time_spent_seconds)
        $timeByAttempt = StudentItemAttempt::query()
            ->whereIn('activity_attempt_id', $attemptIds)
            ->select([
                'activity_attempt_id',
                DB::raw('SUM(time_spent_seconds) as total_seconds'),
            ])
            ->groupBy('activity_attempt_id')
            ->get()
            ->keyBy('activity_attempt_id');

        $rows = [];
        foreach ($attempts as $a) {
            $sec = (int) (($timeByAttempt[$a->id]->total_seconds ?? 0));
            $max = (int) ($a->max_score ?? 0);
            $score = (int) ($a->score_obtained ?? 0);
            $pct = $max > 0 ? round(($score / $max) * 100) : 0;

            $rows[] = [
                'attempt_id' => $a->id,
                'lesson_title' => $a->activity?->lesson?->title ?? 'Actividad',
                'completed_at' => $a->completed_at,
                'score' => $score,
                'max' => $max,
                'pct' => $pct,
                'total_seconds' => $sec,
            ];
        }

        $this->attempts = $rows;
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.student-results');
    }
}
