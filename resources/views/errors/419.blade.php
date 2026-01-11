<!DOCTYPE html>
<html lang="es" data-theme="wisteria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sesión Expirada' }} - Laravel Counter</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100 flex items-center justify-center p-4">
    <main id="main-content" class="card w-full max-w-lg bg-base-100 shadow-xl">
        <div class="card-body text-center">
            <!-- Error Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-24 h-24 rounded-full bg-info/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-8xl font-bold text-info/20 mb-2">419</h1>

            <!-- Error Title -->
            <h2 class="text-3xl font-bold text-base-content mb-4">
                {{ $title ?? 'Sesión Expirada' }}
            </h2>

            <!-- Error Message -->
            <p class="text-base-content/70 mb-8">
                {{ $message ?? 'Su sesión ha expirado. Por favor, recargue la página e inténtelo nuevamente.' }}
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="window.location.reload()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Recargar Página
                </button>
                <a href="{{ route('login') }}" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Iniciar Sesión
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-4 bg-base-200 rounded-lg">
                <p class="text-sm text-base-content/60">
                    <strong>Código de error:</strong> 419 - CSRF Token Mismatch
                </p>
                <p class="text-xs text-base-content/50 mt-2">
                    Este error puede ocurrir por inactividad prolongada o navegación muy rápida.
                </p>
            </div>
        </div>
    </main>
</body>
</html>
