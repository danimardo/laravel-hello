<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with pagination - Optimized with select.
     */
    public function getAllUsers(int $perPage = 15)
    {
        return $this->userRepository->getAllUsersPaginated($perPage);
    }

    /**
     * Get all users for admin (no pagination) - Optimized.
     */
    public function getAllUsersForAdmin()
    {
        return $this->userRepository->getAllUsersForAdmin();
    }

    /**
     * Get user by ID.
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        // Validate data
        $this->validateUserData($data);

        // Check uniqueness
        if (!$this->userRepository->isUsernameUnique($data['username'])) {
            throw ValidationException::withMessages([
                'username' => 'El nombre de usuario ya existe',
            ]);
        }

        if (!$this->userRepository->isEmailUnique($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'El email ya existe',
            ]);
        }

        // Create user
        $user = $this->userRepository->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => 'active',
        ]);

        // Log user creation
        Log::info('User created successfully', [
            'created_user_id' => $user->id,
            'created_username' => $user->username,
            'created_email' => $user->email,
            'created_role' => $user->role,
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? 'system',
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return $user;
    }

    /**
     * Update a user.
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => 'Usuario no encontrado',
            ]);
        }

        // Check if trying to modify admin user
        if ($this->isAdminUser($user) && $this->isModifyingAdminUser($user, $data)) {
            throw ValidationException::withMessages([
                'admin' => 'No se puede modificar el usuario administrador especial',
            ]);
        }

        // Validate data
        $this->validateUserUpdateData($data, $user);

        // Check uniqueness if username or email changed
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if (!$this->userRepository->isUsernameUnique($data['username'], $user->id)) {
                throw ValidationException::withMessages([
                    'username' => 'El nombre de usuario ya existe',
                ]);
            }
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            if (!$this->userRepository->isEmailUnique($data['email'], $user->id)) {
                throw ValidationException::withMessages([
                    'email' => 'El email ya existe',
                ]);
            }
        }

        // Update user
        $updatedUser = $this->userRepository->update($user, $data);

        // Log user update
        Log::info('User updated successfully', [
            'updated_user_id' => $updatedUser->id,
            'updated_username' => $updatedUser->username,
            'updated_email' => $updatedUser->email,
            'updated_role' => $updatedUser->role,
            'updated_status' => $updatedUser->status,
            'changes' => $data,
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? 'system',
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return $updatedUser;
    }

    /**
     * Deactivate a user.
     */
    public function deactivateUser(int $id): User
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => 'Usuario no encontrado',
            ]);
        }

        // Check if trying to deactivate admin user
        if ($this->isAdminUser($user)) {
            throw ValidationException::withMessages([
                'admin' => 'No se puede desactivar el usuario administrador especial',
            ]);
        }

        // Deactivate user
        $user = $this->userRepository->deactivate($user);

        // Log user deactivation
        Log::warning('User deactivated', [
            'deactivated_user_id' => $user->id,
            'deactivated_username' => $user->username,
            'deactivated_email' => $user->email,
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? 'system',
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        // Terminate active sessions (would need session management implementation)
        // $this->terminateUserSessions($user->id);

        return $user;
    }

    /**
     * Activate a user.
     */
    public function activateUser(int $id): User
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => 'Usuario no encontrado',
            ]);
        }

        // Activate user and reset attempts
        $user = $this->userRepository->activate($user);

        // Log user activation
        Log::info('User activated', [
            'activated_user_id' => $user->id,
            'activated_username' => $user->username,
            'activated_email' => $user->email,
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? 'system',
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return $user;
    }

    /**
     * Delete a user (soft delete).
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => 'Usuario no encontrado',
            ]);
        }

        // Check if trying to delete admin user
        if ($this->isAdminUser($user)) {
            throw ValidationException::withMessages([
                'admin' => 'No se puede eliminar el usuario administrador especial',
            ]);
        }

        // Use deactivate instead of delete
        $this->deactivateUser($id);

        // Log user deletion attempt (deactivation)
        Log::warning('User deletion attempted (deactivated instead)', [
            'deleted_user_id' => $user->id,
            'deleted_username' => $user->username,
            'deleted_email' => $user->email,
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? 'system',
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return true;
    }

    /**
     * Check if user is the special admin user.
     */
    private function isAdminUser(User $user): bool
    {
        return $user->username === 'admin';
    }

    /**
     * Check if trying to modify admin user properties.
     */
    private function isModifyingAdminUser(User $user, array $data): bool
    {
        // If username is admin and we're trying to change it
        if ($user->username === 'admin' && isset($data['username']) && $data['username'] !== 'admin') {
            return true;
        }

        // If role is admin and we're trying to change it
        if ($user->role === 'admin' && isset($data['role']) && $data['role'] !== 'admin') {
            return true;
        }

        // If status is being changed to inactive
        if (isset($data['status']) && $data['status'] === 'inactive') {
            return true;
        }

        return false;
    }

    /**
     * Validate user creation data.
     */
    private function validateUserData(array $data): void
    {
        if (!isset($data['username']) || strlen(trim($data['username'])) < 3) {
            throw ValidationException::withMessages([
                'username' => 'El nombre de usuario debe tener al menos 3 caracteres',
            ]);
        }

        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'Debe proporcionar un email válido',
            ]);
        }

        if (!isset($data['password']) || strlen($data['password']) < 6) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña debe tener al menos 6 caracteres',
            ]);
        }

        if (!isset($data['role']) || !in_array($data['role'], ['admin', 'user'])) {
            throw ValidationException::withMessages([
                'role' => 'El rol debe ser admin o user',
            ]);
        }
    }

    /**
     * Validate user update data.
     */
    private function validateUserUpdateData(array $data, User $user): void
    {
        if (isset($data['username'])) {
            $username = trim($data['username']);
            if (strlen($username) < 3) {
                throw ValidationException::withMessages([
                    'username' => 'El nombre de usuario debe tener al menos 3 caracteres',
                ]);
            }
            $data['username'] = $username;
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'email' => 'Debe proporcionar un email válido',
                ]);
            }
            $data['email'] = trim($data['email']);
        }

        if (isset($data['password']) && $data['password']) {
            if (strlen($data['password']) < 6) {
                throw ValidationException::withMessages([
                    'password' => 'La contraseña debe tener al menos 6 caracteres',
                ]);
            }
        } else {
            unset($data['password']);
        }

        if (isset($data['role']) && !in_array($data['role'], ['admin', 'user'])) {
            throw ValidationException::withMessages([
                'role' => 'El rol debe ser admin o user',
            ]);
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            throw ValidationException::withMessages([
                'status' => 'El estado debe ser active o inactive',
            ]);
        }
    }

    /**
     * Get users count by role - Optimized with single query.
     */
    public function getUsersCountByRole(): array
    {
        return $this->userRepository->getUsersCountByRole();
    }

    /**
     * Search users - Optimized to prevent N+1.
     */
    public function searchUsers(string $query)
    {
        return $this->userRepository->searchUsers($query);
    }
}
