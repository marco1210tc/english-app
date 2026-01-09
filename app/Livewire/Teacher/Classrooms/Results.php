<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class Results extends Component
{
    public Classroom $classroom;

    public $rows = []; // array de filas para la tabla

    public function mount(Classroom $classroom): void
    {
        $this->classroom = $classroom;
        // Policy/Gate: mismo criterio que se usa en LessonsManager
        Gate::authorize('manage', $this->classroom);

        $this->loadResults();
    }

    public function loadResults(): void
    {
        // MVP: resumimos por estudiante dentro del classroom
        // score_obtained/max_score se calcula sobre activity_attempts (completados)
        // tiempo total lo sacamos sumando student_item_attempts.time_spent_seconds

        $students = Student::query()
            ->where('classroom_id', $this->classroom->id)
            ->select(['id', 'first_name', 'last_name', 'code'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $studentIds = $students->pluck('id')->all();

        // 1) stats de attempts (solo completed)
        $attemptStats = StudentActivityAttempt::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->select([
                'student_id',
                DB::raw('COUNT(*) as attempts_completed'),
                DB::raw('MAX(completed_at) as last_completed_at'),
                DB::raw('SUM(score_obtained) as score_sum'),
                DB::raw('SUM(max_score) as max_sum'),
            ])
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // 2) tiempo total (sum de item attempts)
        $timeStats = StudentItemAttempt::query()
            ->join('student_activity_attempts as saa', 'saa.id', '=', 'student_item_attempts.activity_attempt_id')
            ->whereIn('saa.student_id', $studentIds)
            ->where('saa.status', 'completed')
            ->select([
                'saa.student_id',
                DB::raw('SUM(student_item_attempts.time_spent_seconds) as total_seconds'),
            ])
            ->groupBy('saa.student_id')
            ->get()
            ->keyBy('student_id');

        // 3) armar filas
        $rows = [];
        foreach ($students as $s) {
            $a = $attemptStats[$s->id] ?? null;
            $t = $timeStats[$s->id] ?? null;

            $max   = (int) ($a->max_sum ?? 0);
            $score = (int) ($a->score_sum ?? 0);
            $pct = $max > 0 ? round(($score / $max) * 100) : 0;

            $rows[] = [
                'student_id' => $s->id,
                'name' => trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: $s->code,
                'code' => $s->code,
                'attempts_completed' => (int) ($a->attempts_completed ?? 0),
                'last_completed_at' => $a?->last_completed_at,
                'score' => $score,
                'max' => $max,
                'pct' => $pct,
                'total_seconds' => (int) ($t->total_seconds ?? 0),
            ];
        }

        // Orden: primero los que sí tienen last_completed_at (no null), luego por fecha desc, luego por nombre
        usort($rows, function ($x, $y) {
            $xNull = empty($x['last_completed_at']);
            $yNull = empty($y['last_completed_at']);

            // NULL al final
            if ($xNull !== $yNull) {
                return $xNull <=> $yNull; // false(0) antes que true(1)
            }

            // ambos con fecha: más reciente primero
            if (!$xNull && !$yNull) {
                $cmp = strcmp((string)$y['last_completed_at'], (string)$x['last_completed_at']);
                if ($cmp !== 0) return $cmp;
            }

            // desempate por nombre asc
            return strcmp(mb_strtolower($x['name']), mb_strtolower($y['name']));
        });

        $this->rows = $rows;
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.results');
    }
}
