<!DOCTYPE html>
<html lang="es" data-theme="wisteria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña - Laravel Counter</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100">
    <!-- Skip Link for Keyboard Navigation -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary focus:text-primary-content focus:rounded">
        Saltar al contenido principal
    </a>

    <!-- Navigation Header -->
    <div class="navbar bg-primary text-primary-content shadow-lg">
        <div class="navbar-start">
            <div class="dropdown lg:hidden">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 text-base-content rounded-box w-52">
                    <li><a href="{{ route('counter') }}">Contador</a></li>
                    @if(Auth::user() && Auth::user()->isAdmin())
                        <li><a href="{{ route('admin.users.index') }}">Administrar Usuarios</a></li>
                    @endif
                    <li><a href="{{ route('change-password') }}" class="active">Cambiar Contraseña</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-ghost text-error w-full justify-start">
                                Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            <a href="{{ route('counter') }}" class="btn btn-ghost text-xl font-bold">
                🎯 Laravel Counter
            </a>
        </div>

        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                <li><a href="{{ route('counter') }}">Contador</a></li>
                @if(Auth::user() && Auth::user()->isAdmin())
                    <li><a href="{{ route('admin.users.index') }}">Administrar Usuarios</a></li>
                @endif
            </ul>
        </div>

        <div class="navbar-end hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                <li><a href="{{ route('change-password') }}" class="active font-semibold">Cambiar Contraseña</a></li>
                <li>
                    <details>
                        <summary class="btn btn-ghost">
                            {{ Auth::user()->username ?? 'Usuario' }}
                        </summary>
                        <ul class="p-2 bg-base-100 text-base-content">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-error w-full text-left">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <main id="main-content" class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-3xl font-bold text-center mb-6 text-primary">
                        Cambiar Contraseña
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

                    @if($errors->any())
                        <div class="alert alert-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                @foreach($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </span>
                        </div>
                    @endif

                    <form action="{{ route('change-password') }}" method="POST" id="changePasswordForm" class="space-y-4">
                        @csrf

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Contraseña Actual</span>
                            </label>
                            <input
                                type="password"
                                name="current_password"
                                placeholder="Ingresa tu contraseña actual"
                                class="input input-bordered w-full @error('current_password') input-error @enderror"
                                autocomplete="current-password"
                                required
                            />
                            @error('current_password')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Nueva Contraseña</span>
                            </label>
                            <input
                                type="password"
                                name="new_password"
                                placeholder="Ingresa tu nueva contraseña"
                                class="input input-bordered w-full @error('new_password') input-error @enderror"
                                autocomplete="new-password"
                                required
                                x-data
                                x-on:input="$dispatch('password-updated', { value: $event.target.value })"
                            />
                            <label class="label">
                                <span class="label-text-alt">Debe contener: mayúscula, minúscula, número y carácter especial</span>
                            </label>
                            @error('new_password')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Password Strength Indicator -->
                        <div x-data="{}" x-on:password-updated.window="if ($event.detail.value) { $nextTick(() => { Alpine.store('passwordStrength', $event.detail.value) }) }">
                            <template x-if="document.querySelector('input[name=new_password]')?.value">
                                @include('components.password-strength-indicator', ['value' => ''])
                            </template>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Confirmar Nueva Contraseña</span>
                            </label>
                            <input
                                type="password"
                                name="new_password_confirmation"
                                placeholder="Confirma tu nueva contraseña"
                                class="input input-bordered w-full @error('new_password_confirmation') input-error @enderror"
                                autocomplete="new-password"
                                required
                            />
                            @error('new_password_confirmation')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control mt-6">
                            <button
                                type="submit"
                                class="btn btn-primary w-full"
                                id="changePasswordButton"
                            >
                                <span id="changePasswordButtonText">Cambiar Contraseña</span>
                                <span id="changePasswordButtonSpinner" class="loading loading-spinner hidden"></span>
                            </button>
                        </div>
                    </form>

                    <div class="divider">Opciones</div>

                    <div class="text-center">
                        <a href="{{ route('counter') }}" class="btn btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver al Contador
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const button = document.getElementById('changePasswordButton');
            const buttonText = document.getElementById('changePasswordButtonText');
            const spinner = document.getElementById('changePasswordButtonSpinner');

            button.disabled = true;
            buttonText.textContent = 'Cambiando contraseña...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
