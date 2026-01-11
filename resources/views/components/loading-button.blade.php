@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, accent, ghost, link
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'loadingText' => 'Procesando...',
    'disabled' => false,
])

@php
    $variantClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'ghost' => 'btn-ghost',
        'link' => 'btn-link',
        'error' => 'btn-error',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
    ];

    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
@endphp

<button
    type="{{ $type }}"
    class="btn {{ $variantClasses[$variant] ?? 'btn-primary' }} {{ $sizeClasses[$size] ?? '' }} {{ $loading ? 'btn-disabled' : '' }}"
    @disabled($disabled || $loading)
    aria-busy="{{ $loading ? 'true' : 'false' }}"
    {{ $attributes }}
>
    @if($loading)
        <span class="loading loading-spinner loading-sm"></span>
        <span>{{ $loadingText }}</span>
    @else
        {{ $slot }}
    @endif
</button>
