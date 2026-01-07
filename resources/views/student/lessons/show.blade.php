@extends('layouts.student')

@section('content')
@php
    $title = $assignment->lesson->title ?? 'Lección';
    $due = $assignment->due_at ? \Illuminate\Support\Carbon::parse($assignment->due_at)->toDateString() : null;
    $itemsCount = $assignment->lesson->relationLoaded('vocabulary')
        ? $assignment->lesson->vocabulary->count()
        : null;
@endphp

<div class="max-w-5xl mx-auto px-4 py-6 space-y-5">

    <div class="rounded-3xl bg-white border p-6">
        <div class="text-sm text-slate-500">Lección</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold mt-2">{{ $title }}</h1>

        <div class="mt-3 text-slate-700 font-semibold text-base sm:text-lg">
            @if($due)
            📅 Entrega: {{ $due }}
            @else
            ⏳ Sin fecha límite
            @endif
        </div>

        @if(!is_null($itemsCount))
        <div class="mt-2 text-slate-600">
            🔤 Palabras: {{ $itemsCount }}
        </div>
        @endif

        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('student.session.play', $assignment->id) }}"
                class="inline-flex items-center justify-center rounded-3xl px-6 py-4 bg-slate-900 text-white font-extrabold text-lg">
                Empezar 🚀
            </a>

            <a href="{{ route('student.lessons.index') }}"
                class="inline-flex items-center justify-center rounded-3xl px-6 py-4 bg-white border font-extrabold text-lg">
                Volver
            </a>
        </div>
    </div>

    {{-- Tips infantiles (carga cognitiva baja) --}}
    <div class="rounded-3xl bg-white border p-6">
        <div class="text-lg font-extrabold">¿Cómo se juega? 🎧</div>
        <ul class="mt-3 space-y-2 text-slate-700 text-base sm:text-lg">
            <li>1) Mira y escucha.</li>
            <li>2) Elige la respuesta.</li>
            <li>3) Al final haces un mini-quiz.</li>
        </ul>
    </div>

</div>
@endsection