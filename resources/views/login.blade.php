@extends('layouts.auth')

@section('content')
@php
// Podrías usar query param ?role=teacher para precargar valor
$initialRole = request('role', 'student'); // 'student' | 'teacher'

// Si hubo error de PIN o se envió un code, volvemos al paso 2
$shouldStartOnStep2 = old('code') || $errors->has('pin');
$initialStep = $shouldStartOnStep2 ? 2 : 1;
@endphp

<div x-data="{
      role: '{{ $initialRole }}',
      step: {{ $initialStep }},
      selectedCode: '{{ old('code', '') }}',
      selectedName: '{{ old('student_name', '') }}',
  }" class="space-y-6">
  {{-- Encabezado --}}
  <header class="space-y-2">
    <p class="text-xs font-semibold tracking-wide text-primary-600 uppercase">
      Bienvenido a EnglishApp
    </p>
    <h2 class="text-2xl font-bold leading-tight">
      Inicia sesión
    </h2>
    <p class="text-sm text-text-muted">
      Elige tu tipo de acceso para continuar.
    </p>
  </header>

  {{-- Selector de rol --}}
  <div class="inline-flex rounded-full bg-surface shadow-sm p-1 text-xs font-medium">
    <button type="button" class="px-4 py-2 rounded-full transition-colors" :class="role === 'student'
          ? 'bg-primary-500 text-white'
          : 'text-text-muted hover:text-text'" @click="role = 'student'">
      Soy estudiante
    </button>
    <button type="button" class="px-4 py-2 rounded-full transition-colors" :class="role === 'teacher'
          ? 'bg-secondary-500 text-white'
          : 'text-text-muted hover:text-text'" @click="role = 'teacher'">
      Soy docente
    </button>
  </div>

  {{-- FORMULARIO ESTUDIANTE (MULTIPASO) --}}
  <form x-show="role === 'student'" x-cloak method="POST" action="{{ route('student.login.submit') }}" class="space-y-4">
    @csrf

    {{-- Campos hidden para mandar code y nombre al backend si los necesitas --}}
    <input type="hidden" name="code" x-model="selectedCode">
    <input type="hidden" name="student_name" x-model="selectedName">

    {{-- PASO 1: elegir estudiante --}}
    <template x-if="step === 1">
      <div class="space-y-3">
        <div class="flex items-center justify-between gap-2">
          <p class="text-xs font-semibold text-text">
            Elige tu nombre
          </p>
          @if($students->count() > 15)
          <p class="text-[0.7rem] text-text-muted">
            Lista larga, puedes desplazar hacia abajo.
          </p>
          @endif
        </div>

        {{-- Panel scrolleable --}}
        <div class="rounded-2xl border border-neutral-200/70 bg-surface/90 p-2 max-h-52 sm:max-h-64 overflow-y-auto">
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
            @foreach($students as $student)
            @php
            $fullName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));

            $parts = preg_split('/\s+/', trim($fullName));

            $first = $parts[0] ?? '';
            $second = $parts[1] ?? '';

            $shortName = $first;
            if ($second !== '') {
            $shortName .= ' ' . mb_substr($second, 0, 1) . '.';
            }

            $initial = mb_substr($first, 0, 1);
            @endphp

            <button type="button"
              class="group flex flex-col items-center justify-center rounded-2xl border-[3px] px-2 py-2 bg-surface transition-all duration-150 ease-out transform-gpu"
              :class="selectedCode === '{{ $student->code }}'
                ? 'border-primary-500 bg-primary-100/80 scale-[1.02] shadow-md'
                : 'border-neutral-200 hover:border-secondary-300 hover:bg-secondary-100/50'" @click="
            selectedCode = '{{ $student->code }}';
            selectedName = '{{ addslashes($fullName) }}'; // para el paso 2 mostramos el nombre completo
            step = 2;
          ">
              <div
                class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full bg-primary-500/90 text-white text-sm sm:text-base mb-1 shadow-sm">
                <span class="font-bold">
                  @isset ($student->avatar)
                      <img src="{{ $student->avatar }}" class="w-full rounded-full">            
                  @else
                      {{ $initial }}
                  @endisset
                </span>
              </div>
              <span
                class="text-[0.75rem] sm:text-[0.8rem] font-semibold text-text text-center leading-snug break-words max-h-[2.5rem] overflow-hidden">
                {{ $shortName }}
              </span>
            </button>
            @endforeach
          </div>
        </div>

        <p class="text-[0.7rem] text-text-muted text-center">
          Toca tu nombre para continuar.
          @if($students->count() > 15)
          <span class="block">
            Si no lo ves, desplázate hacia abajo en la lista.
          </span>
          @endif
        </p>
      </div>
    </template>

    {{-- PASO 2: mostrar seleccionado + PIN --}}
    <template x-if="step === 2">
      <div class="space-y-4">
        {{-- Resumen del estudiante seleccionado --}}
        <div class="flex items-center gap-3 rounded-2xl bg-surface/95 border border-neutral-200/70 px-3 py-3">
          <div
            class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-500 text-white text-lg shadow-sm">
            <span class="font-bold" x-text="selectedName ? selectedName.charAt(0) : '🙂'"></span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-text truncate" x-text="selectedName || 'Estudiante'"></p>
            <p class="text-[0.75rem] text-text-muted">
              Confirma que eres tú y escribe tu PIN de 4 dígitos.
            </p>
          </div>
          <button type="button" class="text-[0.9rem] text-secondary-600 hover:underline" @click="step = 1">
            Cambiar
          </button>
        </div>

        {{-- PIN --}}
        <x-ui.kid-input id="pin" name="pin" type="password" label="PIN" hint="4 dígitos" :required="true" :center="true"
          placeholder="...." inputmode="numeric" maxlength="4" :error="$errors->first('pin')" />

        <x-ui.kid-button type="submit" :uppercase="false" class="w-full">
          Entrar al aula
        </x-ui.kid-button>

        <p class="text-[11px] text-text-muted text-center">
          Si necesitas ayuda, pide a tu docente o apoderado que te apoye con tu PIN.
        </p>
      </div>
    </template>
  </form>

  {{-- Formulario docente --}}
  <form x-show="role === 'teacher'" x-cloak method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    {{-- Email --}}
    <div class="space-y-1.5">
      <label for="email" class="text-xs font-semibold text-text">Correo electrónico</label>
      <input id="email" name="email" type="email" required autocomplete="email" class="w-full rounded-xl border border-neutral-200 bg-surface px-3 py-2.5 text-sm
          placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-secondary-500
          focus:ring-offset-2 focus:ring-offset-background" placeholder="docente@colegio.edu">
    </div>

    {{-- Contraseña --}}
    <div class="space-y-1.5">
      <div class="flex items-center justify-between gap-2">
        <label for="password" class="text-xs font-semibold text-text">Contraseña</label>
        <a href="{{ route('password.request') }}" class="text-[11px] text-secondary-600 hover:underline">
          ¿Olvidaste tu contraseña?
        </a>
      </div>

      <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-neutral-200 bg-surface px-3 py-2.5 text-sm
                     placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-secondary-500
                     focus:ring-offset-2 focus:ring-offset-background" placeholder="••••••••">
    </div>

    {{-- Recordarme --}}
    <div class="flex items-center justify-between gap-2">
      <label class="inline-flex items-center gap-2 text-xs text-text-muted select-none">
        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-neutral-300 text-secondary-600
                         focus:ring-secondary-500">
        <span>Recordarme en este dispositivo</span>
      </label>
    </div>

    {{-- Botón --}}
    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-secondary-600
                 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-500
                 transition-colors focus-visible:outline-none focus-visible:ring-2
                 focus-visible:ring-offset-2 focus-visible:ring-secondary-600
                 focus-visible:ring-offset-background">
      Entrar como docente
    </button>

    <p class="text-[11px] text-text-muted text-center">
      Si aún no tienes cuenta, ponte en contacto con el administrador de tu centro educativo.
    </p>
  </form>

</div>
@endsection