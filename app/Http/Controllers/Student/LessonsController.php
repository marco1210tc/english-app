<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ClassroomLessonAssignment;

class LessonsController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();

        $assignments = ClassroomLessonAssignment::query()
            ->with(['lesson.module']) 
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active') // ✅ solo activas
            // primero con fecha (no null), luego sin fecha
            ->orderByRaw('due_at IS NULL ASC')
            ->orderBy('due_at', 'asc')
            ->orderByDesc('id')
            ->get();

        return view('student.lessons.index', compact('assignments'));
    }

    public function show(int $assignmentId)
    {
        $student = auth('student')->user();

        $assignment = ClassroomLessonAssignment::query()
            ->with(['lesson.module', 'lesson.vocabulary'])
            ->where('classroom_id', $student->classroom_id)
            ->where('status', 'active') // ✅ evita ver cerradas
            ->findOrFail($assignmentId);

        return view('student.lessons.show', compact('assignment'));
    }
}
