<!DOCTYPE html>
<html lang="es" class="h-full">

@include('partials.head', ['title' => 'Iniciar sesión - EnglishApp'])
@livewireStyles

<body class="relative min-h-screen bg-background text-text antialiased font-sans overflow-x-hidden">
  {{-- Fondo con sombras geométricas, aislado y sin overflow --}}
  <div class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -top-24 -left-10 h-40 w-40 rounded-full bg-primary-100 opacity-70 blur-2xl"></div>
    <div class="absolute -bottom-24 -right-6 h-48 w-48 rounded-[2.5rem] bg-secondary-100 opacity-70 blur-2xl"></div>
    <div class="absolute top-10 right-10 h-10 w-10 rounded-2xl bg-accent-100/70"></div>
    <div class="absolute bottom-12 left-12 h-8 w-8 rounded-full bg-purple-100/80"></div>
  </div>

  <div class="relative min-h-screen flex items-center justify-center px-4 py-6">
    {{-- Card principal de auth --}}
    <div class="w-full max-w-lg lg:max-w-xl bg-surface/95 rounded-3xl shadow-2xl border border-neutral-100/70 backdrop-blur-sm">
      <div class="px-6 sm:px-8 py-6 sm:py-8 space-y-6">
        {{-- Header compacto: logo + contexto mínimo --}}
        <header class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-2xl bg-primary-500 flex items-center justify-center shadow-md">
            {{-- Logo placeholder --}}
            <span class="text-xl font-extrabold text-white tracking-tight">
              EA
            </span>
          </div>
          <div class="space-y-0.5">
            <p class="text-xs font-semibold tracking-wide text-primary-600 uppercase">
              EnglishApp
            </p>
            <p class="text-[11px] text-text-muted">
              Acceso a tu aula del colegio
            </p>
          </div>
        </header>

        {{-- Contenido específico (login) --}}
        <main>
          @yield('content')
        </main>
      </div>
    </div>
  </div>

  @livewireScripts
</body>

</html>
