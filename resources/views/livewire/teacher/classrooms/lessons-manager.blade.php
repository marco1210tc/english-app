<div class="space-y-4">
  <h1 class="text-lg font-bold">
    {{ $classroom->name }} — Gestión de lecciones
  </h1>

  @if (session('success'))
  <div class="p-2 border rounded">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
  <div class="p-2 border rounded">
    @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
  </div>
  @endif

  {{-- Form: asignar varias lecciones --}}
  <form method="POST" action="{{ route('teacher.classrooms.lessons.assign', $classroom) }}" class="space-y-3">
    @csrf

    @foreach ($lessons as $l)
    <div class="border rounded p-3 space-y-2">
      <label class="flex gap-2 items-start">
        <input type="checkbox" name="lessons[{{ $loop->index }}][id]" value="{{ $l['id'] }}">

        <div class="flex-1">
          <div class="font-semibold">
            {{ $l['module_title'] }} — {{ $l['title'] }}
          </div>
          <div class="text-sm opacity-70">
            {{ $l['description'] }}
          </div>
        </div>
      </label>

      <div class="pl-6">
        <label class="text-xs">Fecha límite</label>
        <input type="date" name="lessons[{{ $loop->index }}][due_at]"
          value="{{ $assignedMap[$l['id']]['due_at'] ?? '' }}" class="border rounded px-2 py-1">
      </div>

      @if(isset($assignedMap[$l['id']]))
      <div class="pl-6 text-xs">
        Estado: <b>{{ $assignedMap[$l['id']]['status'] }}</b>
      </div>
      @endif
    </div>
    @endforeach

    <button class="border rounded px-4 py-2">
      Guardar asignaciones
    </button>
  </form>

</div>