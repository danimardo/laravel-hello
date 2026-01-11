<div class="error-boundary p-6">
    <div class="alert alert-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
            <h3 class="font-bold">Error en el Componente</h3>
            <div class="text-xs">
                @if($errorMessage)
                    <p class="mt-1">{{ $errorMessage }}</p>
                @else
                    <p class="mt-1">Ha ocurrido un error inesperado en este componente.</p>
                @endif

                @if($showDetails)
                    <div class="mt-2 p-2 bg-base-200 rounded text-left">
                        <p class="font-mono text-xs">
                            <strong>URL:</strong> {{ request()->url() }}<br>
                            <strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}<br>
                            @if($errorCode)
                                <strong>Código:</strong> {{ $errorCode }}
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="flex gap-2 mt-4 justify-center">
        <button
            wire:click="retry"
            class="btn btn-primary btn-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reintentar
        </button>

        <button
            wire:click="toggleDetails"
            class="btn btn-ghost btn-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $showDetails ? 'Ocultar Detalles' : 'Ver Detalles' }}
        </button>

        <button
            onclick="window.location.reload()"
            class="btn btn-ghost btn-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Recargar Página
        </button>
    </div>
</div>
