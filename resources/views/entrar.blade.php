@extends('layouts.auth')

@section('content')
@php
// Podrías usar query param ?role=teacher para precargar valor
$initialRole = request('role', 'student'); // 'student' | 'teacher'
@endphp

<div x-data="{ role: '{{ $initialRole }}' }" class="space-y-6">
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

  {{-- Formulario estudiante --}}
  <form x-show="role === 'student'" x-cloak method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <x-ui.kid-input id="class_code" name="class_code" label="Código de aula" hint="Te lo da tu docente" :required="true"
      :error="$errors->first('class_code')" />

    <x-ui.kid-input id="pin" name="pin" type="password" label="PIN" hint="4 dígitos" :required="true" :center="true"
      placeholder="...." inputmode="numeric" maxlength="4" :error="$errors->first('pin')" />

    <x-ui.kid-button type="submit" :uppercase=false>Entrar al aula</x-ui.kid-button>

    <p class="text-[11px] text-text-muted text-center">
      Este acceso está pensado para que los niños entren rápido a su aula con ayuda del docente.
    </p>
  </form>

  {{-- Formulario docente --}}
  <form x-show="role === 'teacher'" x-cloak method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div class="space-y-1.5">
      <label for="email" class="text-xs font-semibold text-text">
        Correo electrónico
      </label>
      <input id="email" name="email" type="email" required autocomplete="email"
        class="w-full rounded-xl border border-neutral-200 bg-surface px-3 py-2.5 text-sm placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 focus:ring-offset-background"
        placeholder="docente@colegio.edu">
    </div>

    <div class="space-y-1.5">
      <div class="flex items-center justify-between gap-2">
        <label for="password" class="text-xs font-semibold text-text">
          Contraseña
        </label>
        <a href="{{ route('password.request') }}" class="text-[11px] text-secondary-600 hover:underline">
          ¿Olvidaste tu contraseña?
        </a>
      </div>
      <input id="password" name="password" type="password" required autocomplete="current-password"
        class="w-full rounded-xl border border-neutral-200 bg-surface px-3 py-2.5 text-sm placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 focus:ring-offset-background"
        placeholder="••••••••">
    </div>

    <div class="flex items-center justify-between gap-2">
      <label class="inline-flex items-center gap-2 text-xs text-text-muted select-none">
        <input type="checkbox" name="remember"
          class="h-4 w-4 rounded border-neutral-300 text-secondary-600 focus:ring-secondary-500">
        <span>Recordarme en este dispositivo</span>
      </label>
    </div>

    <button type="submit"
      class="w-full inline-flex items-center justify-center rounded-xl bg-secondary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-500 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-secondary-600 focus-visible:ring-offset-background">
      Entrar como docente
    </button>

    <p class="text-[11px] text-text-muted text-center">
      Si aún no tienes cuenta, ponte en contacto con el administrador de tu centro educativo.
    </p>
  </form>
</div>

{{-- <x-ui.kid-card :clickable="true" state="correct" title="Apple">
  <x-slot name="icon">🍎</x-slot>
</x-ui.kid-card>

<x-ui.kid-card :clickable="true" state="incorrect" title="Banana">
  <x-slot name="icon">🍌</x-slot>
</x-ui.kid-card>

<x-ui.kid-card
    :clickable="true"
    state="selected"
    title="Cat"
>
    <x-slot name="icon">🐱</x-slot>
</x-ui.kid-card>

<x-ui.kid-card
    :clickable="true"
    state="default"
    title="Dog"
>
    <x-slot name="icon">🐶</x-slot>
</x-ui.kid-card> --}}

{{-- <x-ui.kid-card
    :clickable="false"
    title="Colores"
    subtitle="10 actividades"
>
    <x-slot name="icon">🎨</x-slot> --}}
    {{-- Contenido extra: por ejemplo, nivel/etiquetas --}}
    {{-- <div class="flex items-center gap-2 text-[0.75rem]">
        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-yellow-700 font-semibold">
            Nivel 1
        </span>
        <span class="text-text-muted">~ 5 min</span>
    </div>
</x-ui.kid-card> --}}

@endsection