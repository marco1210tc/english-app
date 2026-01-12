<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\StudentActivityAttempt;
use App\Models\StudentItemAttempt;
use Illuminate\Support\Facades\Gate;

class AttemptExportController extends Controller
{
    public function export(Classroom $classroom, StudentActivityAttempt $attempt)
    {
        Gate::authorize('manage', $classroom);

        // Cargar relaciones mínimas para validar y nombrar
        $attempt->loadMissing([
            'student:id,classroom_id,first_name,last_name,code',
            'activity.lesson:id,title',
        ]);

        // seguridad: attempt debe pertenecer al classroom
        abort_unless((int)($attempt->student?->classroom_id) === (int)$classroom->id, 404);

        $items = StudentItemAttempt::query()
            ->where('activity_attempt_id', $attempt->id)
            ->orderBy('id')
            ->get([
                'id',
                'item_key',
                'is_correct',
                'attempts',
                'time_spent_seconds',
                'hints_used',
                'response_json',
                'created_at',
            ]);

        $studentName = trim(($attempt->student->first_name ?? '') . ' ' . ($attempt->student->last_name ?? ''))
            ?: ($attempt->student->code ?? 'student');

        $lessonTitle = $attempt->activity?->lesson?->title ?? 'actividad';
        $completedAt = $attempt->completed_at ? $attempt->completed_at->format('Ymd_His') : now()->format('Ymd_His');

        $filename = "attempt_{$attempt->id}_{$attempt->student->code}_{$completedAt}.csv";

        return response()->streamDownload(function () use ($attempt, $items, $studentName, $lessonTitle) {
            $out = fopen('php://output', 'w');

            // BOM Excel UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Metadata arriba (opcional, ayuda mucho al docente)
            fputcsv($out, ['Student', $studentName]);
            fputcsv($out, ['Code', $attempt->student->code ?? '']);
            fputcsv($out, ['Lesson', $lessonTitle]);
            fputcsv($out, ['Attempt ID', $attempt->id]);
            fputcsv($out, ['Started At', optional($attempt->started_at)->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Completed At', optional($attempt->completed_at)->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Score', (int)($attempt->score_obtained ?? 0)]);
            fputcsv($out, ['Max', (int)($attempt->max_score ?? 0)]);
            fputcsv($out, []); // línea en blanco

            // Headers “detalle”
            fputcsv($out, [
                'ItemID',
                'Type',
                'ItemKey',
                'Correct',
                'Attempts',
                'TimeSeconds',
                'HintsUsed',
                'CreatedAt',
                'ResponseJSON',
            ]);

            foreach ($items as $it) {
                $type = null;
                if (is_array($it->response_json)) {
                    $type = $it->response_json['type'] ?? null;
                }

                fputcsv($out, [
                    $it->id,
                    $type ?: '',
                    $it->item_key,
                    $it->is_correct ? 1 : 0,
                    (int)$it->attempts,
                    (int)$it->time_spent_seconds,
                    (int)$it->hints_used,
                    optional($it->created_at)->format('Y-m-d H:i:s'),
                    // JSON plano para auditoría / depuración
                    $it->response_json ? json_encode($it->response_json, JSON_UNESCAPED_UNICODE) : '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
