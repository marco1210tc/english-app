@extends('layouts.student')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-5">

  <div class="rounded-3xl bg-white border p-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Mis lecciones 📚</h1>
    <p class="text-slate-600 mt-2 text-base sm:text-lg">
      Elige una lección y practica paso a paso.
    </p>
  </div>

  @if($assignments->isEmpty())
  <div class="rounded-3xl bg-white border p-8 text-lg text-slate-700">
    Aún no tienes lecciones asignadas.
  </div>
  @else
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach($assignments as $a)
    @php
    $title = $a->lesson->title ?? 'Lección';
    $module = $a->lesson->module->title ?? null; // ajusta si el campo se llama distinto
    $due = $a->due_at ? \Illuminate\Support\Carbon::parse($a->due_at)->toDateString() : null;
    @endphp

    <a href="{{ route('student.lessons.show', $a->id) }}"
      class="group rounded-3xl bg-white border p-6 hover:bg-slate-50 transition flex flex-col gap-4">

      <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
          <div class="text-xl font-extrabold leading-tight">
            {{ $title }}
          </div>

          @if($module)
          <div class="text-slate-600 text-sm">Tema: {{ $module }}</div>
          @endif
        </div>

        <div
          class="shrink-0 h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-extrabold">
          ▶
        </div>
      </div>

      <div class="flex items-center justify-between gap-3">
        <div class="text-slate-700 font-semibold">
          @if($due)
          📅 Entrega: {{ $due }}
          @else
          ⏳ Sin fecha límite
          @endif
        </div>

        <div class="text-slate-500 text-sm group-hover:text-slate-700">
          Toca para entrar
        </div>
      </div>

    </a>
    @endforeach
  </div>
  @endif

</div>
@endsection