<div class="w-full max-w-md mx-auto">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h2 class="card-title text-2xl font-bold text-primary mb-4">
                Contador
            </h2>

            <!-- Display Counter Value with Animation -->
            <div
                class="text-7xl font-bold text-secondary mb-8 transition-all duration-500 ease-in-out"
                id="counterDisplay"
                x-data="{ counterValue: {{ $counter }}, animate: false }"
                x-init="
                    $watch('counterValue', (newVal, oldVal) => {
                        animate = true;
                        if (newVal > oldVal) {
                            counterDisplay.classList.add('scale-110', 'text-success');
                        } else if (newVal < oldVal) {
                            counterDisplay.classList.add('scale-90', 'text-error');
                        } else {
                            counterDisplay.classList.add('animate-spin');
                        }

                        setTimeout(() => {
                            counterDisplay.classList.remove('scale-110', 'scale-90', 'text-success', 'text-error', 'animate-spin');
                            animate = false;
                        }, 300);
                    });
                "
            >
                <span x-text="counterValue"></span>
            </div>

            <!-- Action Buttons with Hover Effects -->
            <div class="grid grid-cols-3 gap-4 w-full">
                <!-- Decrement Button -->
                <button
                    wire:click="decrement"
                    class="btn btn-error btn-lg hover:scale-105 active:scale-95 transition-all duration-200"
                    :class="{ 'btn-disabled opacity-50': $isLoading }"
                    :disabled="$isLoading"
                    title="Decrementar (-1)"
                >
                    @if($isLoading)
                        <span class="loading loading-spinner loading-md"></span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    @endif
                </button>

                <!-- Reset Button -->
                <button
                    wire:click="reset"
                    class="btn btn-warning btn-lg hover:scale-105 active:scale-95 transition-all duration-200"
                    :class="{ 'btn-disabled opacity-50': $isLoading }"
                    :disabled="$isLoading"
                    title="Resetear a 0"
                >
                    @if($isLoading)
                        <span class="loading loading-spinner loading-md"></span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    @endif
                </button>

                <!-- Increment Button -->
                <button
                    wire:click="increment"
                    class="btn btn-success btn-lg hover:scale-105 active:scale-95 transition-all duration-200"
                    :class="{ 'btn-disabled opacity-50': $isLoading }"
                    :disabled="$isLoading"
                    title="Incrementar (+1)"
                >
                    @if($isLoading)
                        <span class="loading loading-spinner loading-md"></span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    @endif
                </button>
            </div>

            <!-- Counter Info with Status Indicator -->
            <div class="mt-6 text-sm text-base-content/70">
                <div class="flex items-center justify-center gap-2">
                    <span class="badge badge-xs"
                        :class="{
                            'badge-success': counterValue > 0,
                            'badge-error': counterValue < 0,
                            'badge-warning': counterValue === 0
                        }"
                        x-text="counterValue > 0 ? 'Positivo' : (counterValue < 0 ? 'Negativo' : 'Cero')"
                    ></span>
                    <p>Valor actual: <strong x-text="counterValue"></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Listen for counter updates
        Livewire.on('counterUpdated', (data) => {
            const display = document.getElementById('counterDisplay');
            if (display) {
                // Add animation class
                display.classList.add('animate-bounce');
                setTimeout(() => {
                    display.classList.remove('animate-bounce');
                }, 500);
            }
        });

        // Add click animations to buttons
        document.querySelectorAll('button[wire\\:click]').forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('animate-ping');
                setTimeout(() => {
                    this.classList.remove('animate-ping');
                }, 300);
            });
        });
    });

    // Update counter display on Livewire updates
    document.addEventListener('livewire:navigated', () => {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('counterValue', {{ $counter }});
        }
    });
</script>
