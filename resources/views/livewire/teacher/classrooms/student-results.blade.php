<div class="max-w-6xl mx-auto p-6 space-y-4">

  <div class="bg-white border rounded-2xl p-6">
    <div class="text-sm text-slate-600">Sección</div>
    <h1 class="text-2xl font-extrabold mt-1">
      {{ $classroom->name }} — Detalle del estudiante
    </h1>

    <div class="mt-3 text-slate-700 font-semibold">
      {{ trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: $student->code }}
      <span class="text-slate-500 font-normal">({{ $student->code }})</span>
    </div>

    <div class="mt-4 flex gap-3">
      <a href="{{ route('teacher.classrooms.results', $classroom) }}"
        class="bg-white border rounded-2xl px-4 py-2 font-semibold hover:bg-slate-50">
        Volver a resultados
      </a>
    </div>
  </div>

  <div class="bg-white border rounded-2xl p-6 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="text-left text-slate-600 border-b">
          <th class="py-3 pr-4">Actividad / Lección</th>
          <th class="py-3 pr-4">Último completado</th>
          <th class="py-3 pr-4">Puntaje</th>
          <th class="py-3 pr-4">% Acierto</th>
          <th class="py-3 pr-0">Tiempo</th>
          <th class="py-3 pr-0">Detalle</th>
        </tr>
      </thead>

      <tbody>
        @forelse($attempts as $r)
        <tr class="border-b last:border-0">
          <td class="py-3 pr-4 font-semibold text-slate-900">
            {{ $r['lesson_title'] }}
          </td>

          <td class="py-3 pr-4 text-slate-700">
            @if($r['completed_at'])
            {{ \Illuminate\Support\Carbon::parse($r['completed_at'])->format('Y-m-d H:i') }}
            @else
            —
            @endif
          </td>

          <td class="py-3 pr-4 text-slate-700">
            {{ $r['score'] }} / {{ $r['max'] }}
          </td>

          <td class="py-3 pr-4">
            <div class="flex items-center gap-2">
              <div class="w-32 bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="h-2 bg-slate-900" style="width: {{ $r['pct'] }}%"></div>
              </div>
              <span class="font-semibold text-slate-900">{{ $r['pct'] }}%</span>
            </div>
          </td>

          <td class="py-3 pr-0 text-slate-700">
            @php
            $sec = (int) $r['total_seconds'];
            $min = intdiv($sec, 60);
            $rem = $sec % 60;
            @endphp
            {{ $min }}m {{ $rem }}s
          </td>
          <td class="py-3 pr-0">
            <a class="font-semibold hover:underline"
              href="{{ route('teacher.classrooms.attempts.show', [$classroom, $r['attempt_id']]) }}">
              Ver →
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="py-6 text-slate-700">
            Aún no hay intentos completados para este estudiante.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>