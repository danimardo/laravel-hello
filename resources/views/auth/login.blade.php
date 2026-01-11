<!DOCTYPE html>
<html lang="es" data-theme="wisteria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Laravel Counter</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100 flex items-center justify-center p-4">
    <!-- Skip Link for Keyboard Navigation -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary focus:text-primary-content focus:rounded">
        Saltar al contenido principal
    </a>

    <main id="main-content" class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-3xl font-bold text-center mb-6 text-primary">
                Iniciar Sesión
            </h2>

            @if(session('error'))
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->has('login'))
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('login') }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm" class="space-y-4" novalidate>
                @csrf

                <div class="form-control w-full">
                    <label class="label" for="username-input">
                        <span class="label-text font-semibold">Usuario o Email</span>
                    </label>
                    <input
                        id="username-input"
                        type="text"
                        name="username"
                        placeholder="Ingresa tu usuario o email"
                        class="input input-bordered w-full @error('username') input-error @enderror"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                        aria-required="true"
                        aria-describedby="username-error"
                    />
                    @error('username')
                        <label class="label" for="username-input">
                            <span id="username-error" class="label-text-alt text-error" role="alert">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label" for="password-input">
                        <span class="label-text font-semibold">Contraseña</span>
                    </label>
                    <input
                        id="password-input"
                        type="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        autocomplete="current-password"
                        required
                        aria-required="true"
                        aria-describedby="password-error"
                    />
                    @error('password')
                        <label class="label" for="password-input">
                            <span id="password-error" class="label-text-alt text-error" role="alert">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control mt-6">
                    <button
                        type="submit"
                        class="btn btn-primary w-full"
                        id="loginButton"
                        aria-label="Iniciar sesión en la aplicación"
                        aria-describedby="login-help"
                    >
                        <span id="loginButtonText">Iniciar Sesión</span>
                        <span id="loginButtonSpinner" class="loading loading-spinner hidden" aria-hidden="true"></span>
                    </button>
                    <span id="login-help" class="sr-only">Presione el botón para iniciar sesión en la aplicación</span>
                </div>
            </form>

            {{-- Credenciales de prueba eliminadas por seguridad --}}
        </div>
    </main>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('loginButtonText');
            const spinner = document.getElementById('loginButtonSpinner');

            button.disabled = true;
            buttonText.textContent = 'Iniciando sesión...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
