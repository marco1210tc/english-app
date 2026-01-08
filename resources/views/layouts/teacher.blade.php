<!DOCTYPE html>
<html lang="es" class="h-full">

  @include('partials.head', ['title' => $title ?? 'Panel del docente - EnglishApp'])
  @livewireStyles

  <body class="relative min-h-screen bg-background text-text antialiased font-sans overflow-x-hidden">

    {{-- Contenido principal --}}
    <main class="flex-1">
      <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 py-5 sm:py-6">
        @yield('content')
      </div>
    </main>

    @livewireScripts
  </body>

</html>