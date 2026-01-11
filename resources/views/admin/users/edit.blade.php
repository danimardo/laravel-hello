@extends('layouts.admin-head')

@section('title', 'Editar Usuario - Laravel Counter')

@section('navbar-center')
    <li><a href="{{ route('admin.users.edit', $user->id) }}" class="font-semibold">Editar Usuario</a></li>
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
                Editar Usuario: {{ $user->username }}
            </h1>
            <p class="text-base-content/70">
                Modifica los datos del usuario seleccionado
            </p>
        </div>

        <!-- Warning for Admin User -->
        @if($user->username === 'admin')
            <div class="alert alert-warning mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>
                    <strong>Usuario Administrador Especial:</strong> No se puede cambiar el rol ni desactivar este usuario.
                </span>
            </div>
        @endif

        <!-- User Info Card -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h3 class="card-title">Información del Usuario</h3>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-base-content/70">ID</p>
                        <p class="font-bold">{{ $user->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Creado</p>
                        <p class="font-bold">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Actualizado</p>
                        <p class="font-bold">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Estado Actual</p>
                        <span class="badge badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'error' : 'warning') }}">
                            @if($user->status === 'active')
                                Activo
                            @elseif($user->status === 'inactive')
                                Inactivo
                            @else
                                Bloqueado
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editUserForm">
                    @csrf
                    @method('PUT')

                    <!-- Username Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Nombre de Usuario *</span>
                            @if($user->username === 'admin')
                                <span class="label-text-alt text-warning">No modificable</span>
                            @endif
                        </label>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            class="input input-bordered w-full @error('username') input-error @enderror {{ $user->username === 'admin' ? 'input-disabled' : '' }}"
                            autocomplete="username"
                            {{ $user->username === 'admin' ? 'disabled' : '' }}
                            required
                        />
                        @error('username')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Email *</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                            autocomplete="email"
                            required
                        />
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Password Fields -->
                    <div class="divider">Cambiar Contraseña (opcional)</div>

                    <!-- New Password Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Nueva Contraseña</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            placeholder="Dejar en blanco para no cambiar"
                            class="input input-bordered w-full @error('password') input-error @enderror"
                            autocomplete="new-password"
                        />
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Mínimo 6 caracteres (opcional)</span>
                        </label>
                    </div>

                    <!-- Confirm New Password Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Confirmar Nueva Contraseña</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="input input-bordered w-full"
                            autocomplete="new-password"
                        />
                    </div>

                    <!-- Role Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Rol *</span>
                            @if($user->username === 'admin')
                                <span class="label-text-alt text-warning">No modificable</span>
                            @endif
                        </label>
                        <div class="join w-full">
                            <input type="radio" name="role" value="user" class="radio radio-bordered join-item @error('role') radio-error @enderror" {{ old('role', $user->role) === 'user' ? 'checked' : '' }} {{ $user->username === 'admin' ? 'disabled' : '' }} />
                            <div class="btn btn-outline join-item flex-1">
                                <div class="text-center">
                                    <div class="font-bold">Usuario</div>
                                    <div class="text-xs text-base-content/70">Acceso solo al contador</div>
                                </div>
                            </div>
                        </div>
                        <div class="join w-full mt-2">
                            <input type="radio" name="role" value="admin" class="radio radio-bordered join-item @error('role') radio-error @enderror" {{ old('role', $user->role) === 'admin' ? 'checked' : '' }} {{ $user->username === 'admin' ? 'disabled' : '' }} />
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

                    <!-- Status Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Estado *</span>
                            @if($user->username === 'admin')
                                <span class="label-text-alt text-warning">No modificable</span>
                            @endif
                        </label>
                        <div class="join w-full">
                            <input type="radio" name="status" value="active" class="radio radio-bordered join-item @error('status') radio-error @enderror" {{ old('status', $user->status) === 'active' ? 'checked' : '' }} {{ $user->username === 'admin' ? 'disabled' : '' }} />
                            <div class="btn btn-outline join-item flex-1">
                                <div class="text-center">
                                    <div class="font-bold text-success">Activo</div>
                                    <div class="text-xs text-base-content/70">Puede iniciar sesión</div>
                                </div>
                            </div>
                        </div>
                        <div class="join w-full mt-2">
                            <input type="radio" name="status" value="inactive" class="radio radio-bordered join-item @error('status') radio-error @enderror" {{ old('status', $user->status) === 'inactive' ? 'checked' : '' }} {{ $user->username === 'admin' ? 'disabled' : '' }} />
                            <div class="btn btn-outline join-item flex-1">
                                <div class="text-center">
                                    <div class="font-bold text-error">Inactivo</div>
                                    <div class="text-xs text-base-content/70">No puede iniciar sesión</div>
                                </div>
                            </div>
                        </div>
                        @error('status')
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
                            <span id="buttonText">Actualizar Usuario</span>
                            <span id="buttonSpinner" class="loading loading-spinner hidden"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const button = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const spinner = document.getElementById('buttonSpinner');

            button.disabled = true;
            button.classList.add('btn-disabled');
            buttonText.textContent = 'Actualizando...';
            spinner.classList.remove('hidden');
        });
    </script>
    @endpush

    @include('layouts.admin-scripts')
@endsection
