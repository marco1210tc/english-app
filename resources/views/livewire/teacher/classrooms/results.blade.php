<div class="max-w-6xl mx-auto p-6 space-y-4">

  <div class="bg-white border rounded-2xl p-6">
    <div class="text-sm text-slate-600">Sección</div>
    <h1 class="text-2xl font-extrabold mt-1">
      {{ $classroom->name }} — Resultados
    </h1>
    <p class="text-slate-600 mt-2">
      Resumen por estudiante (solo intentos <b>completed</b>). Ordenado por “último completado” (más reciente primero).
    </p>
  </div>

  <div class="py-2">
    <a href="{{ route('teacher.classrooms.results.export', $classroom) }}"
      class="bg-slate-900 text-white rounded-2xl px-4 py-2 font-semibold hover:bg-slate-800">
      Export CSV
    </a>
  </div>


  <div class="bg-white border rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 sticky top-0 z-10">
          <tr class="text-left text-slate-600 border-b">
            <th class="py-3 px-4">Estudiante</th>
            <th class="py-3 px-4">Código</th>
            <th class="py-3 px-4">Completados</th>
            <th class="py-3 px-4">Último</th>
            <th class="py-3 px-4">Puntaje</th>
            <th class="py-3 px-4">% Acierto</th>
            <th class="py-3 px-4">Tiempo</th>
            <th class="py-3 pr-0">Detalle</th>
          </tr>
        </thead>

        <tbody>
          @forelse($rows as $r)
          @php
          $hasLast = !empty($r['last_completed_at']);

          $sec = (int) ($r['total_seconds'] ?? 0);
          $min = intdiv($sec, 60);
          $rem = $sec % 60;

          // Fecha: evitar parsear null
          $lastFmt = $hasLast
          ? \Illuminate\Support\Carbon::parse($r['last_completed_at'])->format('Y-m-d H:i')
          : null;
          @endphp

          <tr class="border-b last:border-0 {{ $loop->odd ? 'bg-white' : 'bg-slate-50/40' }}">
            <td class="py-3 px-4 font-semibold text-slate-900">
              {{ $r['name'] }}
            </td>

            <td class="py-3 px-4 text-slate-700">
              {{ $r['code'] }}
            </td>

            <td class="py-3 px-4 text-slate-700">
              {{ (int)($r['attempts_completed'] ?? 0) }}
            </td>

            <td class="py-3 px-4 text-slate-700">
              @if($hasLast)
              {{ $lastFmt }}
              @else
              <span
                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold text-slate-700">
                Sin actividad
              </span>
              @endif
            </td>

            <td class="py-3 px-4 text-slate-700">
              {{ (int)($r['score'] ?? 0) }} / {{ (int)($r['max'] ?? 0) }}
            </td>

            <td class="py-3 px-4">
              @php $pct = (int)($r['pct'] ?? 0); @endphp

              <div class="flex items-center gap-2">
                <div class="w-32 bg-slate-100 rounded-full h-2 overflow-hidden">
                  <div class="h-2 bg-slate-900" style="width: {{ $pct }}%"></div>
                </div>
                <span class="font-semibold text-slate-900">{{ $pct }}%</span>
              </div>
            </td>

            <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
              {{ $min }}m {{ str_pad((string)$rem, 2, '0', STR_PAD_LEFT) }}s
            </td>

            <td class="py-3 pr-0">
              <a class="font-semibold text-slate-900 hover:underline"
                href="{{ route('teacher.classrooms.results.student', [$classroom, $r['student_id']]) }}">
                Ver →
              </a>
            </td>

          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-8 px-4 text-slate-700">
              <div class="font-semibold">Sin datos todavía.</div>
              <div class="text-sm text-slate-500 mt-1">
                Puede ser que no haya estudiantes en la sección o que aún no existan intentos <b>completed</b>.
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex gap-3">
    <a href="{{ route('teacher.classrooms.index') }}"
      class="bg-white border rounded-2xl px-4 py-2 font-semibold hover:bg-slate-50">
      Volver
    </a>
  </div>

</div>