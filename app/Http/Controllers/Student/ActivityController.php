<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitActivityAttemptRequest;
use App\Models\Activity;
use App\Models\StudentActivityAttempt;
use App\Services\ActivityEngine\StartActivityAttemptService;
use App\Services\ActivityEngine\FinishActivityAttemptService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        protected StartActivityAttemptService $startActivityAttempt,
        protected FinishActivityAttemptService $finishActivityAttempt,
    ) {}

    /**
     * Mostrar la actividad al estudiante.
     *
     * GET /student/activities/{activity}
     */
    public function show(Request $request, Activity $activity)
    {
        $user = $request->user();
        $student = $user->student;

        // Opcional: verificación de que el estudiante puede acceder a esa actividad
        // (por grado, módulo, aula, etc.). Luego lo afinamos con Policies.
        // $this->authorize('view', $activity);

        // Cargar preguntas y opciones para quiz (si aplica)
        $activity->load(['lesson.module', 'questions.options']);

        // Crear attempt al entrar (MVP simple)
        $attempt = $this->startActivityAttempt->start($activity, $student);

        return view('student.activities.show', [
            'student'  => $student,
            'activity' => $activity,
            'attempt'  => $attempt,
            // Podrías pasar config_json y otros datos que el frontend necesite
        ]);
    }

    /**
     * Recibir las respuestas del estudiante y finalizar el intento.
     *
     * POST /student/activities/{activity}/attempt
     */
    public function submit(SubmitActivityAttemptRequest $request, Activity $activity)
    {
        $user = $request->user();
        $student = $user->student;

        // $this->authorize('attempt', $activity); // Policy futura

        // Si mandas attempt_id desde el form, lo usamos; si no, creamos uno aquí.
        $attempt = null;
        if ($request->filled('attempt_id')) {
            $attempt = StudentActivityAttempt::where('id', $request->input('attempt_id'))
                ->where('student_id', $student->id)
                ->where('activity_id', $activity->id)
                ->first();
        }

        // Unificamos la estructura de payload para el GradeActivityService
        $payload = $this->buildPayloadFromRequest($request, $activity);

        // Finalizar intento (esto corrige, guarda métricas, dispara evento, etc.)
        $attempt = $this->finishActivityAttempt->finish($activity, $student, $payload, $attempt);

        // Para MVP: redirigir a la misma actividad con feedback
        return redirect()
            ->route('student.activities.show', $activity)
            ->with('activity_result', [
                'score'         => $attempt->score,
                'correct_count' => $attempt->correct_count,
                'wrong_count'   => $attempt->wrong_count,
                'attempt_id'    => $attempt->id,
            ]);
    }

    /**
     * Convertir el request a un payload estándar para el motor de actividades.
     */
    protected function buildPayloadFromRequest(SubmitActivityAttemptRequest $request, Activity $activity): array
    {
        $config = $activity->config_json ?? [];
        $type = $config['type'] ?? 'quiz';

        $payload = [];

        if ($type === 'quiz') {
            // Esperamos answers[question_id] = [option_ids...]
            $payload['answers'] = $request->input('answers', []);
        } elseif ($type === 'drag_drop') {
            // Esperamos drag_drop_answers[item_key] = target
            $payload['answers'] = $request->input('drag_drop_answers', []);
        } else {
            // Otros tipos futuros (memory, listen_match, etc.)
            $payload['answers'] = $request->input('answers', []);
        }

        if ($request->filled('started_at')) {
            $payload['started_at'] = $request->input('started_at');
        }

        return $payload;
    }


}
