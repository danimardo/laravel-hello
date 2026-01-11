<!DOCTYPE html>
<html lang="es" data-theme="wisteria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contador - Laravel Counter</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="min-h-screen bg-base-100">
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
                    @if($user->isAdmin())
                        <li><a href="{{ route('admin.users.index') }}">Administrar Usuarios</a></li>
                    @endif
                    <li><a href="{{ route('change-password') }}">Cambiar Contraseña</a></li>
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
                <li><a href="{{ route('counter') }}" class="font-semibold">Contador</a></li>
                @if($user->isAdmin())
                    <li><a href="{{ route('admin.users.index') }}">Administrar Usuarios</a></li>
                @endif
            </ul>
        </div>

        <div class="navbar-end hidden lg:flex">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full bg-secondary text-secondary-content flex items-center justify-center font-bold">
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    </div>
                </div>
                <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 text-base-content rounded-box w-52">
                    <li class="menu-title">
                        <span>{{ $user->username }}</span>
                    </li>
                    <li><a href="{{ route('profile') }}">Perfil</a></li>
                    <li><a href="{{ route('change-password') }}">Cambiar Contraseña</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-error">
                                Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-primary mb-2">
                ¡Bienvenido, {{ $user->username }}!
            </h1>
            <p class="text-base-content/70">
                @if($user->isAdmin())
                    <span class="badge badge-secondary badge-lg">Administrador</span>
                @else
                    <span class="badge badge-accent badge-lg">Usuario</span>
                @endif
            </p>
        </div>

        <!-- Counter Component -->
        <div class="flex justify-center mb-8">
            @livewire('counter')
        </div>

        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold text-secondary mb-4">
                    Acciones Rápidas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('change-password') }}" class="btn btn-outline btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414A6 6 0 1121 9z" />
                        </svg>
                        Cambiar Contraseña
                    </a>

                    @if($user->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Administrar Usuarios
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-error w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-base-content/50">
            <p class="text-sm">
                Laravel Counter PoC - Autenticación y Gestión de Usuarios
            </p>
            <p class="text-xs mt-2">
                Tema: <span class="badge badge-xs badge-secondary">Wisteria</span>
            </p>
        </footer>
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Custom Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);

        // Prevent form double submission
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.classList.add('btn-disabled');
                }
            });
        });
    </script>
</body>
</html>
