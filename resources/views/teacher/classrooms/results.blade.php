@extends('layouts.teacher')

@section('content')
    @livewire('teacher.classrooms.results', compact('classroom'))
@endsection