<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\Gate;

class StudentExportController extends Controller
{
    public function export(Classroom $classroom, Student $student)
    {
        Gate::authorize('manage', $classroom);

        // seguridad: estudiante pertenece a la sección
        abort_unless((int)$student->classroom_id === (int)$classroom->id, 404);

        // Traer attempts completed del estudiante
        $attempts = StudentActivityAttempt::query()
            ->with(['activity.lesson:id,title'])
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->get(['id','activity_id','started_at','completed_at','score_obtained','max_score']);

        $attemptIds = $attempts->pluck('id')->all();

        // Traer todos los items de todos los attempts (en bloque)
        $items = StudentItemAttempt::query()
            ->whereIn('activity_attempt_id', $attemptIds)
            ->orderBy('activity_attempt_id')
            ->orderBy('id')
            ->get([
                'id',
                'activity_attempt_id',
                'item_key',
                'is_correct',
                'attempts',
                'time_spent_seconds',
                'hints_used',
                'response_json',
                'created_at',
            ]);

        // Map rápido attempt_id => metadata
        $attemptMap = [];
        foreach ($attempts as $a) {
            $attemptMap[$a->id] = [
                'completed_at' => $a->completed_at ? $a->completed_at->format('Y-m-d H:i:s') : '',
                'started_at'   => $a->started_at ? $a->started_at->format('Y-m-d H:i:s') : '',
                'lesson_title' => $a->activity?->lesson?->title ?? 'Actividad',
                'score'        => (int)($a->score_obtained ?? 0),
                'max'          => (int)($a->max_score ?? 0),
            ];
        }

        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))
            ?: ($student->code ?? 'student');

        $filename = 'student_' . $student->code . '_classroom_' . $classroom->id . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($classroom, $student, $studentName, $attempts, $items, $attemptMap) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM Excel

            // Metadata arriba
            fputcsv($out, ['Classroom', $classroom->name ?? $classroom->id]);
            fputcsv($out, ['Student', $studentName]);
            fputcsv($out, ['Code', $student->code ?? '']);
            fputcsv($out, ['AttemptsCompleted', $attempts->count()]);
            fputcsv($out, []); // blank line

            // Headers
            fputcsv($out, [
                'AttemptID',
                'AttemptStartedAt',
                'AttemptCompletedAt',
                'LessonTitle',
                'AttemptScore',
                'AttemptMax',
                'ItemID',
                'Type',
                'ItemKey',
                'Correct',
                'Attempts',
                'TimeSeconds',
                'HintsUsed',
                'ItemCreatedAt',
                'ResponseJSON',
            ]);

            foreach ($items as $it) {
                $meta = $attemptMap[$it->activity_attempt_id] ?? [
                    'completed_at' => '',
                    'started_at' => '',
                    'lesson_title' => '',
                    'score' => 0,
                    'max' => 0,
                ];

                $type = null;
                if (is_array($it->response_json)) {
                    $type = $it->response_json['type'] ?? null;
                }

                fputcsv($out, [
                    $it->activity_attempt_id,
                    $meta['started_at'],
                    $meta['completed_at'],
                    $meta['lesson_title'],
                    $meta['score'],
                    $meta['max'],
                    $it->id,
                    $type ?: '',
                    $it->item_key,
                    $it->is_correct ? 1 : 0,
                    (int)$it->attempts,
                    (int)$it->time_spent_seconds,
                    (int)$it->hints_used,
                    optional($it->created_at)->format('Y-m-d H:i:s'),
                    $it->response_json ? json_encode($it->response_json, JSON_UNESCAPED_UNICODE) : '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
