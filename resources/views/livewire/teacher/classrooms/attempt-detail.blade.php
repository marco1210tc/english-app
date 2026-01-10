<div class="max-w-6xl mx-auto p-6 space-y-4">

    <div class="bg-white border rounded-2xl p-6">
        <div class="text-sm text-slate-600">Detalle de intento</div>

        <h1 class="text-2xl font-extrabold mt-1">
            {{ $classroom->name }}
        </h1>

        <div class="mt-3 text-slate-700">
            <div class="font-semibold">
                Estudiante:
                {{ trim(($attempt->student->first_name ?? '').' '.($attempt->student->last_name ?? '')) ?:
                ($attempt->student->code ?? '—') }}
                <span class="text-slate-500 font-normal">({{ $attempt->student->code ?? '—' }})</span>
            </div>

            <div class="mt-1">
                Actividad/Lección:
                <span class="font-semibold">{{ $attempt->activity?->lesson?->title ?? 'Actividad' }}</span>
            </div>

            <div class="mt-1">
                Estado: <span class="font-semibold">{{ $attempt->status }}</span>
                @if($attempt->completed_at)
                — Completado: {{ \Illuminate\Support\Carbon::parse($attempt->completed_at)->format('Y-m-d H:i') }}
                @endif
            </div>

            <div class="mt-1">
                Puntaje: <span class="font-semibold">{{ $attempt->score_obtained }} / {{ $attempt->max_score }}</span>
            </div>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-2 text-sm">
                <div class="border rounded-xl p-3">
                    <div class="text-slate-500">Ítems</div>
                    <div class="font-bold">{{ $summary['count'] }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-slate-500">✅</div>
                    <div class="font-bold">{{ $summary['correct'] }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-slate-500">❌</div>
                    <div class="font-bold">{{ $summary['wrong'] }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-slate-500">Pistas</div>
                    <div class="font-bold">{{ $summary['hints'] }}</div>
                </div>
                <div class="border rounded-xl p-3">
                    <div class="text-slate-500">Tiempo</div>
                    @php
                    $sec = (int)$summary['seconds'];
                    $min = intdiv($sec, 60);
                    $rem = $sec % 60;
                    @endphp
                    <div class="font-bold">{{ $min }}m {{ $rem }}s</div>
                </div>
            </div>

        </div>

        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-center">
            <a href="{{ route('teacher.classrooms.results.student', [$classroom, $attempt->student_id]) }}"
                class="bg-white border rounded-2xl px-4 py-2 font-semibold hover:bg-slate-50">
                Volver al estudiante
            </a>

            <div class="flex-1"></div>

            <div class="flex gap-2">
                <input type="text" wire:model.live="search" placeholder="Buscar (item_key o json)"
                    class="border rounded-xl px-3 py-2 text-sm w-56" />

                <select wire:model.live="game" class="border rounded-xl px-3 py-2 text-sm">
                    <option value="all">Todos los juegos</option>
                    <option value="flashcard">Flashcards</option>
                    <option value="listening">Listening</option>
                    <option value="matching">Matching</option>
                    <option value="multiple_choice">Multiple choice</option>
                    <option value="other">Otros</option>
                </select>

                <select wire:model.live="filterCorrect" class="border rounded-xl px-3 py-2 text-sm">
                    <option value="all">Todos</option>
                    <option value="correct">Correctos</option>
                    <option value="wrong">Incorrectos</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-600 border-b">
                    <th class="py-3 pr-4">#</th>
                    <th class="py-3 pr-4">Item</th>
                    <th class="py-3 pr-4">Juego</th>
                    <th class="py-3 pr-4">OK</th>
                    <th class="py-3 pr-4">Intentos</th>
                    <th class="py-3 pr-4">Pistas</th>
                    <th class="py-3 pr-4">Tiempo</th>
                    <th class="py-3 pr-0">Respuesta (json)</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $idx => $r)
                <tr class="border-b last:border-0 align-top">
                    <td class="py-3 pr-4 text-slate-700">
                        {{ $idx + 1 }}
                    </td>

                    <td class="py-3 pr-4 font-semibold text-slate-900">
                        {{ $r['item_key'] }}
                    </td>

                    <td class="py-3 pr-4 text-slate-700 font-semibold">
                        {{ $r['type'] }}
                    </td>

                    <td class="py-3 pr-4">
                        @if($r['is_correct'])
                        <span class="font-semibold">✅</span>
                        @else
                        <span class="font-semibold">❌</span>
                        @endif
                    </td>

                    <td class="py-3 pr-4 text-slate-700">
                        {{ $r['attempts'] }}
                    </td>

                    <td class="py-3 pr-4 text-slate-700">
                        {{ $r['hints_used'] }}
                    </td>

                    <td class="py-3 pr-4 text-slate-700">
                        @php
                        $sec = (int)$r['time_spent_seconds'];
                        $min = intdiv($sec, 60);
                        $rem = $sec % 60;
                        @endphp
                        {{ $min }}m {{ $rem }}s
                    </td>

                    <td class="py-3 pr-0">
                        <pre
                            class="text-xs bg-slate-50 border rounded-xl p-3 whitespace-pre-wrap break-words">{{ json_encode($r['response_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-6 text-slate-700">
                        No hay ítems para este intento.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>