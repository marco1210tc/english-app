@props([
    'id',
    'label' => null,
    'hint' => null,
    'type' => 'text',
    'error' => null,
    'center' => false,
    'required' => false,
    'inputmode' => null,
    'maxlength' => null,
    'placeholder' => null,
])

@php
    $inputId = $id ?? $attributes->get('id');

    // Base del input
    $baseInputClasses = implode(' ', [
        'block w-full rounded-2xl bg-surface',
        'px-4 py-3',
        'text-[1.05rem] sm:text-[1.1rem] leading-snug',
        'placeholder:text-neutral-400',
        'border-[3px] border-neutral-300',
        'transition-all duration-150 ease-out',
        'focus:outline-none',
        'transform-gpu', // para animaciones suaves
    ]);

    // Estado visual: normal, hover, focus, error
    $stateClasses = $error
        ? 'border-red-100 bg-red-10'
        : '
            hover:border-primary-300
            focus:border-primary-500
            focus:scale-[1.01]
            active:scale-[1.00]
        ';

    // tracking centrado para PIN
    $alignClasses = $center ? 'text-center tracking-[0.35em]' : 'text-left';

    $finalInputClasses = trim("$baseInputClasses $stateClasses $alignClasses");
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-[0.9rem] sm:text-sm font-semibold text-text">
            {{ $label }}
            @if ($required)
                <span class="text-xs text-red-100 align-middle">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        name="{{ $attributes->get('name') }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($inputmode) inputmode="{{ $inputmode }}" @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        {{ $attributes->merge(['class' => $finalInputClasses]) }}
    >

    @if ($hint)
        <p class="text-[0.7rem] sm:text-[0.75rem] text-text-muted">
            {{ $hint }}
        </p>
    @endif

    @if ($error)
        <p class="text-[0.7rem] sm:text-[0.75rem] font-medium text-red-100">
            {{ $error }}
        </p>
    @endif
</div>
