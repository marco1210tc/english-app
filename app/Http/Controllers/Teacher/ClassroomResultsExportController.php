<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;



class ClassroomResultsExportController extends Controller
{
    public function export(Classroom $classroom)
    {
        // seguridad (policy manage classroom)
        Gate::authorize('manage', $classroom);

        $students = Student::query()
            ->where('classroom_id', $classroom->id)
            ->select(['id', 'first_name', 'last_name', 'code'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $studentIds = $students->pluck('id')->all();

        // 1) stats attempts (solo completed)
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

        // 2) tiempo total (sum item attempts)
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

        // 3) filas
        $rows = [];
        foreach ($students as $s) {
            $a = $attemptStats[$s->id] ?? null;
            $t = $timeStats[$s->id] ?? null;

            $max   = (int) ($a->max_sum ?? 0);
            $score = (int) ($a->score_sum ?? 0);
            $pct = $max > 0 ? round(($score / $max) * 100) : 0;

            $rows[] = [
                'student' => trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: $s->code,
                'code' => $s->code,
                'attempts_completed' => (int) ($a->attempts_completed ?? 0),
                'last_completed_at' => $a?->last_completed_at
                    ? \Illuminate\Support\Carbon::parse($a->last_completed_at)->format('Y-m-d H:i:s')
                    : '',
                'score' => $score,
                'max' => $max,
                'pct' => $pct,
                'total_seconds' => (int) ($t->total_seconds ?? 0),
            ];
        }

        // Orden recomendado: último intento DESC, luego nombre
        usort($rows, function ($x, $y) {
            $xNull = empty($x['last_completed_at']);
            $yNull = empty($y['last_completed_at']);

            // NULL al final
            if ($xNull !== $yNull) {
                return $xNull <=> $yNull;
            }

            // ambos con fecha: más reciente primero
            if (!$xNull && !$yNull) {
                $cmp = strcmp((string)$y['last_completed_at'], (string)$x['last_completed_at']);
                if ($cmp !== 0) return $cmp;
            }

            // desempate por nombre asc
            return strcmp(mb_strtolower($x['student']), mb_strtolower($y['student']));
        });

        // CSV streaming
        $filename = 'resultados_' . $classroom->id . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // BOM para Excel (UTF-8)
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // headers
            fputcsv($out, [
                'Estudiante',
                'Codigo',
                'Completados',
                'Ultimo',
                'Puntaje',
                'Max',
                'Porcentaje',
                // 'Tiempo_segundos', //total en segundos
                'Tiempo_mmss',
            ]);

            foreach ($rows as $r) {
                $sec = (int) $r['total_seconds'];
                $min = intdiv($sec, 60);
                $rem = $sec % 60;
                fputcsv($out, [
                    $r['student'],
                    $r['code'],
                    $r['attempts_completed'],
                    $r['last_completed_at'],
                    $r['score'],
                    $r['max'],
                    $r['pct'],
                    // $r['total_seconds'],
                    "{$min}m {$rem}s", // formato legible
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
