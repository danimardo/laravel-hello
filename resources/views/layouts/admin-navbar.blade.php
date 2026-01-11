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
                <li><a href="{{ route('admin.users.index') }}">Usuarios</a></li>
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
            <li><a href="{{ route('counter') }}">Contador</a></li>
            <li><a href="{{ route('admin.users.index') }}">Usuarios</a></li>
            @if(View::hasSection('navbar-center'))
                @yield('navbar-center')
            @endif
        </ul>
    </div>

    <div class="navbar-end hidden lg:flex">
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full bg-secondary text-secondary-content flex items-center justify-center font-bold">
                    A
                </div>
            </div>
            <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 text-base-content rounded-box w-52">
                <li class="menu-title">
                    <span>Administrador</span>
                </li>
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
