@extends('layouts.student')

@section('content')
@php
// Estos vendrían del controlador realmente
// $student, $overallProgress, $nextActivity, $lessons...
$overallProgress = $overallProgress ?? 45; // %
$lessons = $lessons ?? collect(); // colección de lecciones
@endphp

{{-- Saludo + progreso principal --}}
<section class="space-y-4 mb-6">
    <div class="text-white font-bold">
        <form class="px-3 py-6 bg-red-300" action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit"> Logout </button>
        </form>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-text-muted">
                ¡Hola, {{ $student->name ?? 'estudiante' }}! 👋
            </p>
            <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                ¿Listo para seguir aprendiendo inglés?
            </h1>
        </div>

        {{-- Progreso general resumido (pequeño) --}}
        <div class="w-full sm:w-56 mt-2 sm:mt-0">
            <x-ui.kid-progress :percent="$overallProgress" label="Progreso general" :small="true" />
        </div>
    </div>
</section>

{{-- Bloque: continuar donde lo dejaste --}}
<section class="mb-6">
    <div
        class="rounded-3xl bg-surface/95 border border-neutral-200/70 px-4 py-4 sm:px-5 sm:py-4 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-2xl bg-primary-100 flex items-center justify-center text-2xl">
                🚀
            </div>
            <div>
                <p class="text-[0.9rem] sm:text-base font-semibold">
                    Continuar donde te quedaste
                </p>
                <p class="text-[0.8rem] text-text-muted">
                    Retoma tu última lección y no pierdas tu racha.
                </p>
            </div>
        </div>

        <div class="flex-1"></div>

        <div class="w-full sm:w-auto">
            <x-ui.kid-button type="button">
                Continuar
            </x-ui.kid-button>
        </div>
    </div>
</section>

{{-- Lista de lecciones --}}
<section class="space-y-3">
    <div class="flex items-center justify-between gap-2">
        <h2 class="text-xl font-bold">
            Tus lecciones
        </h2>
        {{-- Aquí luego podrías poner un filtro simple o un “Ver todo” --}}
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        @forelse ($lessons as $lesson)
        @php
        // Ejemplos de datos, en la realidad deberían venir del modelo:
        $icon = $lesson->icon ?? '📚';
        $subtitle = $lesson->subtitle ?? "{$lesson->activities_count} actividades";
        $progress = $lesson->progress_percent ?? 0;
        @endphp

        <x-ui.kid-card :clickable="true" title="{{ $lesson->title }}" subtitle="{{ $subtitle }}"
            onclick="window.location='{{ route('student.lessons.show', $lesson) }}'">
            <x-slot name="icon">
                {{ $icon }}
            </x-slot>

            <x-ui.kid-progress :percent="$progress" label="Progreso" :small="true" />
        </x-ui.kid-card>
        @empty
        <p class="text-sm text-text-muted col-span-full">
            Aún no tienes lecciones asignadas. Tu docente las verá pronto aquí.
        </p>
        @endforelse
    </div>
</section>
@endsection