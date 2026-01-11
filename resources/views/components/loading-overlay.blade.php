@props(['show' => false, 'text' => 'Procesando...', 'fullscreen' => false])

@if($show)
    <div class="{{ $fullscreen ? 'fixed inset-0' : 'absolute inset-0' }} z-50 flex items-center justify-center bg-base-100/80 backdrop-blur-sm"
         role="dialog"
         aria-modal="true"
         aria-labelledby="loading-title">
        <div class="flex flex-col items-center justify-center gap-3 p-6 rounded-lg bg-base-100 shadow-xl">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p id="loading-title" class="text-sm font-medium text-base-content">{{ $text }}</p>
        </div>
    </div>
@endif
