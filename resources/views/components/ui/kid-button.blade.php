@props([
    'type' => 'button',
    'full' => true,       // w-full o no
    'uppercase' => true,  // texto en mayúsculas o no
    'primary' => true,
])

@php
    $widthClass = $full ? 'w-full' : 'inline-flex';
    $textCase = $uppercase ? 'uppercase' : '';
    $variants = [
        'primary' => '
            bg-primary-600 border-primary-700
            before:from-primary-600 before:via-primary-600 before:to-primary-500
            focus-visible:ring-primary-500
        ',
        'secondary' => '
            bg-secondary-600 border-secondary-700
            before:from-secondary-600 before:via-secondary-600 before:to-secondary-500
            focus-visible:ring-secondary-500
        ',
    ];

    $variantClass = $primary ? $variants['primary'] : $variants['secondary'];
@endphp

<div class="relative inline-block {{ $widthClass }} h-14 text-[1.3rem]">
    <button
        type="{{ $type }}"
        {{ $attributes->merge([
        'class' => "
            relative overflow-hidden cursor-pointer w-full
            {$textCase} text-white font-bold py-3 px-6
            rounded-2xl border-b-[6px] shadow-md

            transition-[transform,border,box-shadow] duration-100 ease-in-out
            active:translate-y-[3px] active:border-b-[3px] active:shadow-sm

            focus-visible:outline-none
            focus-visible:ring-2
            focus-visible:ring-offset-2
            focus-visible:ring-offset-background

            before:content-['']
            before:absolute before:inset-0
            before:bg-gradient-to-r
            before:-translate-x-full
            before:transition-transform before:duration-700
            hover:before:translate-x-0
            before:z-0

            {$variantClass}
        "
    ]) }}
    >
        <span class="relative z-10">
            {{ $slot }}
        </span>
    </button>
</div>
