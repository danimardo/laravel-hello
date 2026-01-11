@props(['size' => 'md', 'text' => 'Cargando...', 'showText' => true, 'color' => 'primary'])

@php
    $sizeClasses = [
        'sm' => 'loading-sm',
        'md' => '',
        'lg' => 'loading-lg',
    ];

    $colorClasses = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'accent' => 'text-accent',
        'neutral' => 'text-neutral',
    ];
@endphp

<div class="flex flex-col items-center justify-center gap-2 p-4"
     role="status"
     aria-live="polite"
     aria-busy="true">
    <span class="loading loading-{{ $sizeClasses[$size] ?? '' }} loading-spinner text-{{ $colorClasses[$color] ?? 'primary' }}"></span>
    @if($showText)
        <span class="text-sm text-base-content/70">{{ $text }}</span>
    @endif
</div>
