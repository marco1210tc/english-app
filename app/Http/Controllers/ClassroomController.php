<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        // Verificar el rol del usuario
        if ($user->role === 'admin') {
            // Si es un admin, mostrar todos los grupos
            $groups = Classroom::with('teacher')->get();
        } elseif ($user->role === 'teacher') {
            // Si es un profesor, mostrar solo los grupos a su cargo
            $groups = Classroom::where('teacher_id', $user->id)->get();
        } else {
            // En caso de que el rol sea 'student' o cualquier otro, no mostrar grupos (o redirigir)
            $groups = collect(); // O redirigir, si es necesario
        }

        // Retornar la vista con los grupos
        return view('teacher.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = \App\Models\User::where('role', 'student')->get();
        return view('teacher.groups.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $classroom = Classroom::create($request->all());
        $classroom->students()->attach($request->input('students'));
        return redirect()->route('groups');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $group)
    {
        return view('teacher.groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
