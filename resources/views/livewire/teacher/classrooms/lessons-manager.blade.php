<div class="space-y-4">
  <h1 class="text-lg font-bold">
    {{ $classroom->name }} — Gestión de lecciones
  </h1>

  {{-- Mensaje simple (si usas dispatch toast puedes omitir esto) --}}
  @if (session('success'))
    <div class="p-2 border rounded">{{ session('success') }}</div>
  @endif

  {{-- Guardar asignación --}}
  <div class="flex items-center gap-2">
    <button type="button"
            wire:click="assignSelected"
            class="border rounded px-4 py-2 font-semibold">
      Guardar asignaciones
    </button>

    <a href="{{ route('teacher.classrooms.index') }}" class="text-sm underline">
      Volver
    </a>
  </div>

  <div class="space-y-3">
    @foreach ($lessons as $l)
      @php
        $lessonId = (int) $l->id;
        $a = $assigned[$lessonId] ?? null;
      @endphp

      <div class="border rounded p-3 space-y-2">
        <label class="flex gap-2 items-start">
          <input type="checkbox" wire:model="select.{{ $lessonId }}">

          <div class="flex-1">
            <div class="font-semibold">
              {{ $l->module->title ?? 'Módulo' }} — {{ $l->title }}
            </div>
            @if($l->description)
              <div class="text-sm opacity-70">{{ $l->description }}</div>
            @endif
          </div>
        </label>

        <div class="pl-6 flex flex-col sm:flex-row sm:items-center gap-2">
          <div>
            <label class="text-xs block">Fecha límite</label>

            {{-- Si quieres datetime exacto, usa datetime-local (recomendado) --}}
            <input type="datetime-local"
                   wire:model.defer="dueAt.{{ $lessonId }}"
                   class="border rounded px-2 py-1">
          </div>

          @if($a)
            <button type="button"
                    wire:click="updateDueAt({{ $lessonId }})"
                    class="border rounded px-3 py-1 text-xs">
              Actualizar fecha
            </button>
          @endif
        </div>

        @if($a)
          <div class="pl-6 text-xs flex items-center gap-2">
            Estado: <b>{{ $a['status'] }}</b>

            <button type="button"
                    wire:click="toggleStatus({{ $lessonId }})"
                    class="underline">
              {{ $a['status'] === 'active' ? 'Cerrar' : 'Reabrir' }}
            </button>

            <span class="opacity-40">|</span>

            <button type="button"
                    wire:click="unassign({{ $lessonId }})"
                    class="underline text-red-600">
              Quitar
            </button>
          </div>
        @endif
      </div>
    @endforeach
  </div>
</div>
