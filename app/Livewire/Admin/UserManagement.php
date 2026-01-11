<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\UserService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';
    public int $perPage = 15;

    // Loading states
    public bool $isLoading = false;
    public bool $isDeactivating = false;
    public bool $isActivating = false;
    public bool $isDeleting = false;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        $query = User::query();

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Role filter
        if (!empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        // Status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        return $this->userService->getUsersCountByRole();
    }

    public function deactivateUser($userId)
    {
        $this->isDeactivating = true;
        try {
            $this->userService->deactivateUser($userId);
            $this->dispatch('user-updated', message: 'Usuario desactivado exitosamente');
        } catch (\Exception $e) {
            $this->dispatch('user-update-error', message: 'Error al desactivar usuario: ' . $e->getMessage());
        } finally {
            $this->isDeactivating = false;
        }
    }

    public function activateUser($userId)
    {
        $this->isActivating = true;
        try {
            $this->userService->activateUser($userId);
            $this->dispatch('user-updated', message: 'Usuario activado exitosamente');
        } catch (\Exception $e) {
            $this->dispatch('user-update-error', message: 'Error al activar usuario: ' . $e->getMessage());
        } finally {
            $this->isActivating = false;
        }
    }

    public function deleteUser($userId)
    {
        $this->isDeleting = true;
        try {
            $this->userService->deleteUser($userId);
            $this->dispatch('user-updated', message: 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            $this->dispatch('user-update-error', message: 'Error al eliminar usuario: ' . $e->getMessage());
        } finally {
            $this->isDeleting = false;
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->statusFilter = '';
    }

    public function render()
    {
        $users = $this->users;
        $stats = $this->stats;

        return view('livewire.admin.user-management', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}
