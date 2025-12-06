@props([
    'title' => null,
    'subtitle' => null,
    'state' => 'default',   // default | selected | correct | incorrect | disabled
    'clickable' => false,   // si es opción clicable o solo contenedor visual
])

@php
    // Base del contenedor
    $baseClasses = implode(' ', [
        'group relative w-full',
        'rounded-2xl md:rounded-3xl',
        'border-[3px]',
        'px-4 py-3 sm:px-5 sm:py-4',
        'bg-surface',
        'flex items-center gap-3 sm:gap-4',
        'transition-all duration-150 ease-out',
        'transform-gpu',
    ]);

    // Estado visual según "state"
    switch ($state) {
        case 'selected':
            $stateClasses = 'border-secondary-500 bg-secondary-100/70 shadow-md';
            break;
        case 'correct':
            $stateClasses = 'border-primary-500 bg-primary-100/80 shadow-md';
            break;
        case 'incorrect':
            $stateClasses = 'border-red-100 bg-red-10 shadow-sm';
            break;
        case 'disabled':
            $stateClasses = 'border-neutral-200 bg-neutral-100 opacity-70';
            break;
        default:
            $stateClasses = 'border-neutral-200 hover:border-secondary-300';
            break;
    }

    // Interacción (hover, focus, active)
    $interactiveClasses = '';
    if ($clickable && $state !== 'disabled') {
        $interactiveClasses = implode(' ', [
            'cursor-pointer',
            'hover:scale-[1.01]',
            'active:scale-[0.99]',
            'focus-visible:outline-none',
            'focus-visible:border-secondary-500',
            'focus-visible:shadow-lg',
        ]);
    } else {
        $interactiveClasses = 'cursor-default';
    }

    $finalClasses = trim("$baseClasses $stateClasses $interactiveClasses");
@endphp

@if ($clickable)
    <button
        type="button"
        {{ $attributes->merge(['class' => $finalClasses]) }}
    >
        {{-- Icono / emoji / ilustración --}}
        @if (isset($icon))
            <div class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-2xl bg-background-muted text-2xl">
                {{ $icon }}
            </div>
        @endif

        {{-- Contenido --}}
        <div class="flex-1 min-w-0">
            @if ($title)
                <p class="text-[1.05rem] sm:text-[1.1rem] font-semibold text-text truncate">
                    {{ $title }}
                </p>
            @endif

            @if ($subtitle)
                <p class="mt-0.5 text-[0.8rem] sm:text-[0.85rem] text-text-muted truncate">
                    {{ $subtitle }}
                </p>
            @endif

            {{-- Contenido extra (ej. badges, tags, etc.) --}}
            @if (trim($slot) !== '')
                <div class="mt-2">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </button>
@else
    <div {{ $attributes->merge(['class' => $finalClasses]) }}>
        @if (isset($icon))
            <div class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-2xl bg-background-muted text-2xl">
                {{ $icon }}
            </div>
        @endif

        <div class="flex-1 min-w-0">
            @if ($title)
                <p class="text-[1.05rem] sm:text-[1.1rem] font-semibold text-text truncate">
                    {{ $title }}
                </p>
            @endif

            @if ($subtitle)
                <p class="mt-0.5 text-[0.8rem] sm:text-[0.85rem] text-text-muted truncate">
                    {{ $subtitle }}
                </p>
            @endif

            @if (trim($slot) !== '')
                <div class="mt-2">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
@endif
