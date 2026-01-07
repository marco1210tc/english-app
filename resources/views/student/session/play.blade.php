@extends('layouts.student')

@section('content')
  <div class="max-w-5xl mx-auto px-4 py-6">
    @livewire('student.session.player', ['assignmentId' => $assignment->id])
  </div>
@endsection
