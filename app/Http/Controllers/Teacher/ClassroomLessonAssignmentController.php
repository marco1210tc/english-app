<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AssignLessonsRequest;
use App\Http\Requests\Teacher\UpdateLessonAssignmentRequest;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;


class ClassroomLessonAssignmentController extends Controller
{

    public function index(Classroom $classroom)
    {
        $this->authorize('manage', $classroom);

        return view('teacher.classrooms.lessons.assign', compact('classroom'));
    }

    // POST: asignar varias lecciones a una sección
    public function store(AssignLessonsRequest $request, Classroom $classroom)
    {
        $this->authorize('manage', $classroom);

        $teacherId = auth()->id();
        $now = now();

        DB::transaction(function () use ($request, $classroom, $teacherId, $now) {
            foreach ($request->lessons as $row) {
                DB::table('classroom_lesson_assignments')->updateOrInsert(
                    [
                        'classroom_id' => $classroom->id,
                        'lesson_id'    => $row['id'],
                    ],
                    [
                        'assigned_by' => $teacherId,
                        'assigned_at' => $now,
                        'due_at'      => $row['due_at'] ?? null,
                        'status'      => 'active',
                        'updated_at'  => $now,
                        'created_at'  => $now,
                    ]
                );
            }
        });

        return back()->with('success', 'Lecciones asignadas.');
    }

    // PATCH: cambiar estado o due_at de una asignación (1 lesson)
    // Actualizar estado / due_at de UNA lección asignada
    public function update(UpdateLessonAssignmentRequest $request, Classroom $classroom, int $lessonId)
    {
        $this->authorize('manage', $classroom);

        $updated = DB::table('classroom_lesson_assignments')
            ->where('classroom_id', $classroom->id)
            ->where('lesson_id', $lessonId)
            ->update([
                'status'     => $request->status,
                'due_at'     => $request->due_at,
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return back()->withErrors(['assignment' => 'No existe la asignación para esa lección.']);
        }

        return back()->with('success', 'Asignación actualizada.');
    }

    // DELETE: quitar asignación
    public function destroy(Classroom $classroom, int $lessonId)
    {
        $this->authorize('manage', $classroom);

        DB::table('classroom_lesson_assignments')
            ->where('classroom_id', $classroom->id)
            ->where('lesson_id', $lessonId)
            ->delete();

        return back()->with('success', 'Asignación eliminada.');
    }
}
