@extends('layouts.admin-head')

@section('title', 'Gestión de Usuarios - Laravel Counter')

@section('body')
    @include('layouts.admin-navbar')

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-primary mb-2">
                        Gestión de Usuarios
                    </h1>
                    <p class="text-base-content/70">
                        Administra usuarios del sistema, roles y permisos
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('counter') }}" class="btn btn-outline btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Ir al Contador
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @include('components.admin-alert')

        <!-- Livewire Component -->
        <div class="w-full">
            @livewire('admin.user-management')
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-base-content/50">
            <p class="text-sm">
                Panel de Administración - Gestión de Usuarios
            </p>
            <p class="text-xs mt-2">
                Tema: <span class="badge badge-xs badge-secondary">Wisteria</span>
            </p>
        </footer>
    </main>

    @include('layouts.admin-scripts')
@endsection
