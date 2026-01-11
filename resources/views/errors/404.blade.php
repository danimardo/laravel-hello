<!DOCTYPE html>
<html lang="es" data-theme="wisteria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Error 404' }} - Laravel Counter</title>

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
                <div class="w-24 h-24 rounded-full bg-warning/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-8xl font-bold text-warning/20 mb-2">404</h1>

            <!-- Error Title -->
            <h2 class="text-3xl font-bold text-base-content mb-4">
                {{ $title ?? 'Página No Encontrada' }}
            </h2>

            <!-- Error Message -->
            <p class="text-base-content/70 mb-8">
                {{ $message ?? 'La página que busca no existe o ha sido movida.' }}
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="history.back()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver Atrás
                </button>
                <a href="{{ route('counter') }}" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Ir al Inicio
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-4 bg-base-200 rounded-lg">
                <p class="text-sm text-base-content/60">
                    <strong>Código de error:</strong> 404 - Not Found
                </p>
                <p class="text-xs text-base-content/50 mt-2">
                    Verifique que la URL sea correcta o utilice el menú de navegación.
                </p>
            </div>
        </div>
    </main>
</body>
</html>
