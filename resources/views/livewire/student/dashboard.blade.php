<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Saludo + progreso --}}
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
                    ¡Hola, {{ $studentName }}! 👋
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                    ¿Listo para seguir aprendiendo inglés?
                </h1>
            </div>

            <div class="w-full sm:w-56 mt-2 sm:mt-0">
                <x-ui.kid-progress :percent="$overallProgress" label="Progreso general" :small="true" />
            </div>
        </div>
    </section>

    {{-- Bloque 1: Continuar --}}
    <section class="mb-6">
        <div class="rounded-3xl bg-surface/95 border border-neutral-200/70 px-4 py-4 sm:px-5 sm:py-4 shadow-sm
                flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-2xl bg-primary-100 flex items-center justify-center text-2xl">
                    🚀
                </div>
                <div>
                    <p class="text-[0.9rem] sm:text-base font-semibold">
                        @if($continueLessonTitle)
                        Continuar: {{ $continueLessonTitle }}
                        @else
                        Aún no tienes lecciones asignadas
                        @endif
                    </p>

                    <p class="text-[0.8rem] text-text-muted">
                        @if($continueModuleTitle)
                        Tema: {{ $continueModuleTitle }}
                        @else
                        Retoma tu última lección y no pierdas tu racha.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex-1"></div>

            <div class="w-full sm:w-auto flex gap-2">
                @if($continueAssignmentId)
                <x-ui.kid-button type="button" wire:click="goContinue">
                    Continuar
                </x-ui.kid-button>
                <a href="{{ route('student.lessons.show', ['assignmentId' => $continueAssignmentId]) }}">
                    <x-ui.kid-button :primary="false">
                        Ver detalle
                    </x-ui.kid-button>
                </a>
                @else
                <a href="{{ route('student.lessons.index') }}'">
                    <x-ui.kid-button>
                        Ir a mis lecciones
                    </x-ui.kid-button>
                </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Bloque 2: Vence pronto --}}
    <section class="mb-6 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-xl font-bold">Vence pronto ⏰</h2>
            <a class="text-sm text-text-muted hover:text-text" href="{{ route('student.lessons.index') }}">
                Ver todas →
            </a>
        </div>

        @if(collect($dueSoon)->isEmpty())
        <div class="rounded-3xl bg-surface/95 border border-neutral-200/70 p-5 text-text-muted">
            No tienes lecciones con fecha límite por ahora.
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @foreach($dueSoon as $a)
            @php
            $title = $a->lesson->title ?? 'Lección';
            $due = $a->due_at ? \Illuminate\Support\Carbon::parse($a->due_at)->toDateString() : null;
            @endphp

            <x-ui.kid-card :clickable="true" onclick="window.location='{{ route('student.lessons.show', $a->id) }}'">
                <x-slot name="icon">⏳</x-slot>

                <div class="font-semibold">{{ $title }}</div>
                <div class="text-xs text-text-muted mt-1">
                    📅 Entrega: {{ $due }}
                </div>
            </x-ui.kid-card>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Bloque 3: Resumen --}}
    <section class="mb-6 space-y-3">
        <h2 class="text-xl font-bold">Resumen ⭐</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <x-ui.kid-card>
                <x-slot name="icon">⏳</x-slot>
                <div class="text-xs text-text-muted">Pendientes</div>
                <div class="text-2xl font-extrabold">{{ $stats['pending'] }}</div>
            </x-ui.kid-card>

            <x-ui.kid-card>
                <x-slot name="icon">🔥</x-slot>
                <div class="text-xs text-text-muted">En progreso</div>
                <div class="text-2xl font-extrabold">{{ $stats['in_progress'] }}</div>
            </x-ui.kid-card>

            <x-ui.kid-card>
                <x-slot name="icon">✅</x-slot>
                <div class="text-xs text-text-muted">Completadas</div>
                <div class="text-2xl font-extrabold">{{ $stats['completed'] }}</div>
            </x-ui.kid-card>

            <x-ui.kid-card>
                <x-slot name="icon">📚</x-slot>
                <div class="text-xs text-text-muted">Total</div>
                <div class="text-2xl font-extrabold">{{ $stats['total'] }}</div>
            </x-ui.kid-card>
        </div>
    </section>

    {{-- Acceso a index (no redundante: aquí es “ver todo”) --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-xl font-bold">Tus lecciones</h2>
            <a class="text-sm text-text-muted hover:text-text" href="{{ route('student.lessons.index') }}">
                Ir al listado →
            </a>
        </div>

        {{-- Si quieres, aquí NO repitas tarjetas. Mejor un call-to-action. --}}
        <div class="rounded-3xl bg-surface/95 border border-neutral-200/70 p-5">
            <p class="text-sm text-text-muted">
                Aquí verás lo más importante (continuar, vence pronto y resumen).
                Para ver todas las lecciones y sus fechas, entra al listado.
            </p>
        </div>
    </section>

</div>