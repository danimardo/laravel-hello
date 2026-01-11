@extends('layouts.admin-head')

@section('title', 'Crear Usuario - Laravel Counter')

@section('navbar-center')
    <li><a href="{{ route('admin.users.create') }}" class="font-semibold">Crear Usuario</a></li>
@endsection

@section('body')
    @include('layouts.admin-navbar')

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Page Header -->
        <div class="mb-8">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-primary mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Usuarios
            </a>
            <h1 class="text-4xl font-bold text-primary mb-2">
                Crear Nuevo Usuario
            </h1>
            <p class="text-base-content/70">
                Completa los datos para crear un nuevo usuario del sistema
            </p>
        </div>

        <!-- Create Form Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
                    @csrf

                    <!-- Username Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Nombre de Usuario *</span>
                        </label>
                        <input
                            type="text"
                            name="username"
                            placeholder="Ingresa el nombre de usuario"
                            class="input input-bordered w-full @error('username') input-error @enderror"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            required
                        />
                        @error('username')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Mínimo 3 caracteres</span>
                        </label>
                    </div>

                    <!-- Email Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Email *</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            placeholder="usuario@ejemplo.com"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                        />
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Contraseña *</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            placeholder="Mínimo 6 caracteres"
                            class="input input-bordered w-full @error('password') input-error @enderror"
                            autocomplete="new-password"
                            required
                        />
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Mínimo 6 caracteres</span>
                        </label>
                    </div>

                    <!-- Password Confirmation Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Confirmar Contraseña *</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="input input-bordered w-full"
                            autocomplete="new-password"
                            required
                        />
                    </div>

                    <!-- Role Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Rol *</span>
                        </label>
                        <div class="join w-full">
                            <input type="radio" name="role" value="user" class="radio radio-bordered join-item @error('role') radio-error @enderror" {{ old('role', 'user') === 'user' ? 'checked' : '' }} />
                            <div class="btn btn-outline join-item flex-1">
                                <div class="text-center">
                                    <div class="font-bold">Usuario</div>
                                    <div class="text-xs text-base-content/70">Acceso solo al contador</div>
                                </div>
                            </div>
                        </div>
                        <div class="join w-full mt-2">
                            <input type="radio" name="role" value="admin" class="radio radio-bordered join-item @error('role') radio-error @enderror" {{ old('role') === 'admin' ? 'checked' : '' }} />
                            <div class="btn btn-outline join-item flex-1">
                                <div class="text-center">
                                    <div class="font-bold">Administrador</div>
                                    <div class="text-xs text-base-content/70">Acceso completo al sistema</div>
                                </div>
                            </div>
                        </div>
                        @error('role')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="card-actions justify-end">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="submitButton"
                        >
                            <span id="buttonText">Crear Usuario</span>
                            <span id="buttonSpinner" class="loading loading-spinner hidden"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card bg-info/10 border border-info/20 shadow-xl mt-6">
            <div class="card-body">
                <h3 class="card-title text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Información
                </h3>
                <div class="text-sm space-y-2">
                    <p>• Los usuarios con rol <strong>"Usuario"</strong> solo pueden acceder al contador</p>
                    <p>• Los usuarios con rol <strong>"Administrador"</strong> tienen acceso completo al sistema</p>
                    <p>• El usuario administrador especial <strong>"admin"</strong> está protegido y no puede ser eliminado</p>
                    <p>• Todos los usuarios pueden cambiar su contraseña después de iniciar sesión</p>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const button = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const spinner = document.getElementById('buttonSpinner');

            button.disabled = true;
            button.classList.add('btn-disabled');
            buttonText.textContent = 'Creando...';
            spinner.classList.remove('hidden');
        });
    </script>
    @endpush

    @include('layouts.admin-scripts')
@endsection
