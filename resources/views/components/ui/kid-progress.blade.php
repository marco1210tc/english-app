@props([
    'current' => null,   // paso actual (ej: 3)
    'total' => null,     // total de pasos (ej: 8)
    'percent' => null,   // si quieres pasar directamente un porcentaje
    'label' => null,     // texto personalizado (opcional)
    'small' => false,    // versión más compacta si es necesario
])

@php
    // Calcular porcentaje
    if (!is_null($percent)) {
        $percentage = max(0, min(100, (int) $percent));
    } elseif (!is_null($current) && !is_null($total) && $total > 0) {
        $percentage = max(0, min(100, (int) round(($current / $total) * 100)));
    } else {
        $percentage = 0;
    }

    // Label por defecto: "Pregunta X de Y" si hay current/total y no hay label custom
    $autoLabel = null;
    if (is_null($label) && !is_null($current) && !is_null($total)) {
        $autoLabel = "Pregunta {$current} de {$total}";
    }

    $isSmall = (bool) $small;
@endphp

<div class="space-y-1">
    {{-- Etiqueta de progreso --}}
    @if($label || $autoLabel)
        <div class="flex items-center justify-between gap-2 text-[0.8rem] sm:text-[0.85rem]">
            <p class="font-semibold text-text">
                {{ $label ?? $autoLabel }}
            </p>
            @if(!is_null($current) && !is_null($total))
                <p class="text-text-muted">
                    {{ $percentage }}%
                </p>
            @endif
        </div>
    @endif

    {{-- Barra de progreso --}}
    <div
        class="w-full rounded-full bg-background-muted border border-neutral-200/70 overflow-hidden"
        @if($isSmall)
            style="height: 0.6rem;"
        @else
            style="height: 0.9rem;"
        @endif
    >
        <div
            class="h-full rounded-full bg-primary-500 transition-all duration-300 ease-out"
            style="width: {{ $percentage }}%;"
        ></div>
    </div>
</div>
