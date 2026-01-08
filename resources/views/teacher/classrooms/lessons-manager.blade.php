@extends('layouts.teacher')

@section('content')
    <h1 class="text-black"> Seccion de lecciones de {{ $classroom->name }} </h1>
    @livewire('teacher.classrooms.lessons-manager', compact('classroom'))
@endsection