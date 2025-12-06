<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        // Cargar módulos con sus lecciones y actividades (para un dashboard básico)
        $modules = Module::with(['lessons.activities'])
            ->orderBy('order')
            ->get();

        // En el futuro puedes calcular porcentajes de avance, badges, etc.
        return view('student.dashboard.index', [
            'student' => $student,
            'modules' => $modules,
        ]);
    }
}
