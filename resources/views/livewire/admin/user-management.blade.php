<div class="w-full">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Total Usuarios</div>
            <div class="stat-value text-primary">{{ $stats['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Admins</div>
            <div class="stat-value text-secondary">{{ $stats['admins'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Usuarios</div>
            <div class="stat-value text-accent">{{ $stats['users'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Activos</div>
            <div class="stat-value text-success">{{ $stats['active'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Inactivos</div>
            <div class="stat-value text-error">{{ $stats['inactive'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <h3 class="card-title text-xl">Filtros</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Buscar</span>
                    </label>
                    <input
                        type="text"
                        placeholder="Usuario o email..."
                        class="input input-bordered"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Rol</span>
                    </label>
                    <select class="select select-bordered" wire:model.live="roleFilter">
                        <option value="">Todos los roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">Usuario</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Estado</span>
                    </label>
                    <select class="select select-bordered" wire:model.live="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                        <option value="temp_blocked">Bloqueado</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Por página</span>
                    </label>
                    <select class="select select-bordered" wire:model.live="perPage">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            @if($search || $roleFilter || $statusFilter)
                <div class="mt-4">
                    <button class="btn btn-outline btn-sm" wire:click="resetFilters">
                        Limpiar filtros
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h3 class="card-title text-xl">Lista de Usuarios</h3>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Usuario
                </a>
            </div>

            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="font-bold">{{ $user->username }}</div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role === 'admin' ? 'secondary' : 'accent' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'error' : 'warning') }}">
                                            @if($user->status === 'active')
                                                Activo
                                            @elseif($user->status === 'inactive')
                                                Inactivo
                                            @else
                                                Bloqueado
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="flex justify-center gap-2">
                                            <a
                                                href="{{ route('admin.users.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline btn-primary"
                                                title="Editar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            @if($user->username !== 'admin')
                                                @if($user->status === 'active')
                                                    <button
                                                        class="btn btn-sm btn-outline btn-warning"
                                                        wire:click="deactivateUser({{ $user->id }})"
                                                        title="Desactivar"
                                                        onclick="return confirm('¿Está seguro de desactivar este usuario?')"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button
                                                        class="btn btn-sm btn-outline btn-success"
                                                        wire:click="activateUser({{ $user->id }})"
                                                        title="Activar"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                @endif

                                                <button
                                                    class="btn btn-sm btn-outline btn-error"
                                                    wire:click="deleteUser({{ $user->id }})"
                                                    title="Eliminar"
                                                    onclick="return confirm('¿Está seguro de eliminar este usuario?')"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="badge badge-xs badge-ghost" title="Usuario administrador especial">
                                                    Protegido
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="text-xl font-bold mt-4 text-base-content/70">No hay usuarios</h3>
                    <p class="text-base-content/50 mt-2">
                        @if($search || $roleFilter || $statusFilter)
                            No se encontraron usuarios con los filtros aplicados.
                        @else
                            Aún no se han creado usuarios.
                        @endif
                    </p>
                    @if(!($search || $roleFilter || $statusFilter))
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-4">
                            Crear primer usuario
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Toast notifications -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('user-updated', (event) => {
                const toast = document.createElement('div');
                toast.className = 'alert alert-success';
                toast.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>${event.message}</span>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            });

            Livewire.on('user-update-error', (event) => {
                const toast = document.createElement('div');
                toast.className = 'alert alert-error';
                toast.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>${event.message}</span>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            });
        });
    </script>
</div>
