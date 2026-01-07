<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
    @forelse ($lessons as $lesson)
    @php
    // Ejemplos de datos, en la realidad deberían venir del modelo:
    $icon = $lesson->icon ?? '📚';
    // $title = $lesson->title ?? "{$lesson->activities_count} actividades";
    $progress = $lesson->progress_percent ?? 0;
    @endphp

    <x-ui.kid-card :clickable="true" {{--  title="{{ $lesson->title }}" --}}
        onclick="window.location='{{ route('student.lessons.show', $lesson->id) }}'">
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