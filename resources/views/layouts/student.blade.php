<!DOCTYPE html>
<html lang="es" class="h-full">

@include('partials.head', ['title' => $title ?? 'Panel del estudiante - EnglishApp'])
@livewireStyles

<body class="relative min-h-screen bg-background text-text antialiased font-sans overflow-x-hidden">
  {{-- Fondo decorativo para niños --}}
  <div class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -top-24 -left-8 h-40 w-40 rounded-full bg-primary-100 opacity-70 blur-2xl"></div>
    <div class="absolute -bottom-28 -right-4 h-52 w-52 rounded-[2.5rem] bg-secondary-100 opacity-70 blur-2xl"></div>
    <div class="absolute top-16 right-24 h-9 w-9 rounded-2xl bg-accent-100/70"></div>
    <div class="absolute bottom-20 left-16 h-8 w-8 rounded-full bg-purple-100/80"></div>
  </div>

  <div class="relative min-h-screen flex flex-col">
    {{-- Header del estudiante --}}
    <header class="w-full border-b border-neutral-200/70 bg-surface/90 backdrop-blur-sm">
      <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-4">
        {{-- Logo + nombre app --}}
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-2xl bg-primary-500 flex items-center justify-center shadow-md">
            <span class="text-lg font-extrabold text-white tracking-tight">EA</span>
          </div>
          <div class="leading-tight">
            <p class="text-xs font-semibold tracking-wide text-primary-600 uppercase">
              EnglishApp
            </p>
            <p class="text-[0.8rem] text-text-muted">
              Aprender inglés jugando
            </p>
          </div>
        </div>

        {{-- Info del estudiante (placeholder: usa tus datos reales) --}}
        <div class="flex items-center gap-3">
          <div class="text-right leading-tight hidden sm:block">
            <p class="text-[0.85rem] font-semibold">
              {{ $student->name ?? 'Estudiante' }}
            </p>
            @if(isset($student) && isset($student->grade_name))
              <p class="text-[0.7rem] text-text-muted">
                {{ $student->grade_name }}
              </p>
            @endif
          </div>
          <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-primary-500/90 flex items-center justify-center text-white text-lg shadow-md">
            {{-- Inicial del estudiante como avatar por defecto --}}
            <span class="font-bold">
              {{ isset($student) ? mb_substr($student->name, 0, 1) : 'E' }}
            </span>
          </div>
        </div>
      </div>
    </header>

    {{-- Contenido principal --}}
    <main class="flex-1">
      <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 py-5 sm:py-6">
        @yield('content')
      </div>
    </main>
  </div>

  @livewireScripts
</body>

</html>
