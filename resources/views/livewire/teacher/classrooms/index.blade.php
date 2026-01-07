<div class="max-w-5xl mx-auto p-6 space-y-4">
  <div class="bg-white border rounded-2xl p-6">
    <h1 class="text-2xl font-extrabold">Mis secciones</h1>
    <p class="text-slate-600 mt-1">Elige una sección para asignar lecciones.</p>
  </div>

  @if($classrooms->isEmpty())
    <div class="bg-white border rounded-2xl p-6">
      No tienes secciones asignadas.
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @foreach($classrooms as $c)
        <a href="{{ route('teacher.classrooms.lessons', $c) }}"
           class="bg-white border rounded-2xl p-6 hover:bg-slate-50 transition block">
          <div class="text-sm text-slate-600">
            Grado: {{ $c->grade->name ?? $c->grade_id }}
          </div>
          <div class="text-xl font-extrabold mt-1">
            {{ $c->name }}
          </div>
          @if($c->class_code)
            <div class="text-slate-600 mt-2 text-sm">Código: {{ $c->class_code }}</div>
          @endif
          <div class="mt-4 text-slate-700 font-semibold">Gestionar lecciones →</div>
        </a>
      @endforeach
    </div>
  @endif
</div>
